<?php
/**
 * Plugin tcycle: tiny cycle
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Reinhard Kaeferboeck <rfk@kaeferboeck.info>
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_tcycle extends DokuWiki_Syntax_Plugin {
    var $dataspeed='500';
    var $datafx='fade';
    var $datatimeout='4000';
    
    function getType(){ return 'formatting';}
    function getPType(){ return 'normal';}
    function getAllowedTypes() { return array('container','substition','protected','disabled','formatting','paragraphs'); }
    function getSort(){ return 195; }
    function connectTo($mode) { $this->Lexer->addEntryPattern('<tcycle.*?>(?=.*?</tcycle>)',$mode,'plugin_tcycle'); }
    function postConnect() { $this->Lexer->addExitPattern('</tcycle>','plugin_tcycle'); }
  
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state) {
            case DOKU_LEXER_ENTER:
                $attributes = strtolower(substr($match, 5, -1));
                $dataspeed = $this->_getAttribute($attributes, "data-speed", "500");
                $datafx = $this->_getAttribute($attributes, "data-fx", "fade");
                $datatimeout = $this->_getAttribute($attributes, "data-timeout", "4000");

                return array($state, array($dataspeed,$datafx,$datatimeout));
            case DOKU_LEXER_UNMATCHED:
                return array($state, $match);
            case DOKU_LEXER_EXIT:
                return array($state, '');
        }
        return array();
    }
 
    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'xhtml'){
            list($state,$match) = $data;
            switch ($state) {
              case DOKU_LEXER_ENTER :
                list($this->dataspeed,$this->datafx,$this->datatimeout) = $match;
                $renderer->doc .= '<div class="tcycle" ';
				$renderer->doc .= 'data-speed="'.$this->dataspeed.'" ';
				$renderer->doc .= 'data-fx="'.$this->datafx.'" ';
				$renderer->doc .= 'data-timeout="'.$this->datatimeout.'">';
                break;
              case DOKU_LEXER_UNMATCHED :  
                $renderer->doc .= $renderer->_xmlEntities($match);
                break;
              case DOKU_LEXER_EXIT :       
                $renderer->doc .= "</div>"; 
                break;
            }
            return true;
        }
        return false;
    }
	
    function _getAttribute($attributeString, $attribute, $default){
        $retVal = $default;
        $pos = strpos($attributeString, $attribute."=");
        if ($pos === false) {
            $pos = strpos($attributeString, $attribute." ");
        }
        if ($pos > 0) {
            $pos = $pos + strlen($attribute);
            $value = substr($attributeString,$pos);
            
            //replace '=' and quote signs with null and trim leading spaces
            $value = str_replace("=","",$value);
            $value = str_replace("'","",$value);
            $value = str_replace('"','',$value);
            $value = ltrim($value);
            
            //grab the text before the next space
            $pos = strpos($value, " ");
            if ($pos > 0) {
                $value = substr($value,0,$pos);
            }
            
            $retVal = $value;
        }
        return $retVal;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :