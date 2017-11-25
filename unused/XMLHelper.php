<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XML
 *
 * @author dmtuan
 */
class XMLHelper {
    /*
    *   Custom XML-Creator Functions
    *
    */

    private $xml;
    

    public function CreateNewXmlInstance(){
        /*********************************************/
        /** XML DOM example of building XML Request **/
        /*********************************************/
        $this->xml = new DOMDocument('1.0', 'UTF-8');
        return $this->xml;
    }

    public function CreateRootNode($name){
        $rootElement = $this->xml->appendChild( $this->xml->createElement($name) );
        return $rootElement;
    }

    public function AppendNodeWithChild($node, $childName, $childText = ''){
        $result = $node->appendChild($this->xml->createElement($childName));
        if(null != $childText && !empty($childText)){
            $result->appendChild( $this->xml->createTextNode($childText) );
        }

        return $result;
    }

    public function SetNodeAttributes($node, $nameAndValues){
        if(null != $nameAndValues && sizeof($nameAndValues) > 0){
            foreach($nameAndValues as $name => $value){
                $this->SetNodeAttribute($node, $name, $value);
            }
        }
    }

    public function SetNodeAttribute($node, $name, $value){
        $node->setAttribute($name, $value);
    }

    public function SaveXml(){
//        $this->xml->formatOutput = true;
        return $this->xml->saveXML();
    }
    
    public function SaveFile($file){
//        $this->xml->formatOutput = true;
        return $this->xml->save($file);
    }
    
}
    
    
?>
