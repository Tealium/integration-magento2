#!/usr/bin/php
<?php
    $size = count($argv);
    $udoName = '';
    $merges = [];
    $handles = [];

    $firstArg = true;
    $parseMode = 'udo';

    foreach($argv as $arg) {
        if(substr($arg, -1) == "," || substr($arg, -1) == ".") {
            $arg = substr($arg, 0, strlen($arg) - 1);
        }
        
        // the first argument is always the cmd name; ignore it
        if($firstArg) {
            $firstArg = false;
        } else {
            // if the input is a keyword, set the new parse mode
            switch($arg) {
                case 'create':
                case 'creating':
                case 'use':
                    $parseMode = 'udo';
                    break;
                case 'merge':
                case 'merging':
                case 'from':
                    $parseMode = 'merges';
                    break;
                case 'on':
                case 'handle':
                case 'handles':
                case 'for':
                    $parseMode = 'handles';
                    break;
                case 'and':
                case 'to':
                    break;
                
                // argument is not a keyword,
                // so handle the input depending on the parse mode
                default:
                    switch($parseMode) {
                        case 'udo':  // set the udo name
                            $udoName = $arg;
                            break;
                        case 'merges': // append component udo to list for merging
                            array_push($merges, $arg);
                            break;
                        case 'handles':  // append handle name to list of handles
                            array_push($handles, $arg);
                            break;
                        default:  // only run default when in an unknown parse mode
                            break;
                    }
                    break;
            }
        }
    }
    
    /*
     * This function returns a string containing the contents of the block file.
     * It uses the name of the new udo along with the udos that get merged,
     * along with a template to generate this string.
     */
    function blockTemplate($udoName, $merges) {
        // define some stuff to include and begin the class definition
        $template = <<<"EOT"
<?php
namespace Tealium\Tags\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
 
class $udoName extends Udo
EOT;
        // begin the constructor
        $template .= <<<'EOT'
   
{
    public function __construct(
        Context $context,
        array $data = []
EOT;
        
        // add each of the udos to be merged as a constructor argument so that
        // the dependency injector
        
        foreach($merges as $merge) {
                $udoVarName = lcfirst($merge);
                $template .= ",\n        $merge " . '$' . "$udoVarName";
        }
        
        
        // insert comment indicating where other dependencies need to go
        $template .= <<<'EOT'

   
        /*
         * Any other needed dependencies to inject go here
         */
    ) {
EOT;

        // merge each of the component udos. Only the first one uses "$this",
        // the others are chained calls.
        $first = true;
        foreach($merges as $merge) {
            $udoVarName = lcfirst($merge);
            if($first) {
                $first = false;
                $template .= "\n" . '        $this->merge($' . "$udoVarName" . '->getUdoData())';
            } else {
                $template .= "\n" . '            ->merge($' . "$udoVarName" . '->getUdoData())';
            }
        }
        
        // add a newline if things were merged
        if(count($merges) > 0) {
            $template .= ";\n";
        }
        
        // create section for adding any custom data using services
        $template .= <<<'EOT'
        
        $this->merge([
           /*
            * Fill in data here using injected system objects, e.g:
            * 
            * "page_type" => "home",
            * "page_name" => "Home",
            * "site_currency" => "USD",
            * "site_region" => "us",
            *  
            *     . . .
            */ 
        ]);
        
        parent::__construct($context, $data);
    }
}
EOT;

        // All done!
        return $template;
    }
    
    /*
     * This function returns a string containing the contents of a layout file.
     * It uses the name of a handle along with the name of the udo
     * that the template references.
     */
    function layoutTemplate($udoName, $handle) {
        // there's two different templates. One used on "default", and the other
        // for anything else
        
        // default template
        $default = <<<EOT
<?xml version="1.0"?>
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="../../../../../../../lib/internal/Magento/Framework/View/Layout/etc/page_configuration.xsd">        
    <referenceContainer name="after.body.start">
        <block
            template="utag.phtml"
            class="Tealium\Tags\Block\Utag"
            name="tealium.tags.utag"
            ifconfig = "tags/general/enabled"
        />
        <block
            template="udo.phtml"
            class="Tealium\Tags\Block\\$udoName"
            name="tealium.tags.defaultudo"
            ifconfig = "tags/general/enabled"
        />
    </referenceContainer>
</layout>
EOT;
        
        // template for any kind of layout that is not default
        $other = <<<EOT
<?xml version="1.0"?>
<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="../../../../../../../lib/internal/Magento/Framework/View/Layout/etc/page_configuration.xsd">        
    <referenceContainer name="after.body.start">
        <referenceBlock name="tealium.tags.defaultudo" remove="true" />
        <block
            template="udo.phtml"
            class="Tealium\Tags\Block\\$udoName"
            name="tealium.tags.udo"
            ifconfig = "tags/general/enabled"
        />
    </referenceContainer>
</layout>            
EOT;
        
        return $handle == 'default' ? $default : $other;
    }
    
    // create the contents of the block file
    $blockFile = blockTemplate($udoName, $merges);
    
    // write the block file
    if(sizeof($merges) > 0) {
        $file = fopen("./Block/$udoName.php", "w");
        fwrite($file, blockTemplate($udoName, $merges));
        fclose($file);
    }
    
    // create and write the contents of each layout file
    foreach($handles as $handle) {
        $file = fopen("./view/frontend/layout/$handle.xml", "w");
        fwrite($file, layoutTemplate($udoName, $handle));
        fclose($file);
    }
?>
