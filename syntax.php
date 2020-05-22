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
                $attributes  = strtolower(substr($match, 5, -1));
                $dataspeed   = $this->_getAttribute($attributes, "data-speed", "500");
                $datafx      = $this->_getAttribute($attributes, "data-fx", "fade");
                $datatimeout = $this->_getAttribute($attributes, "data-timeout", "4000");
				$width       = $this->_getAttribute($attributes, "width", "600px");
				$height      = $this->_getAttribute($attributes, "height", "400px");
                $namespace   = $this->_getAttribute($attributes, "namespace", "");

                return array($state, array($dataspeed,$datafx,$datatimeout, $width, $height, $namespace));
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
                list($this->dataspeed,$this->datafx,$this->datatimeout,$this->width,$this->height,$this->namespace) = $match;
				$renderer->doc .= '<div class="tcycle" style="width: '.$this->width.';"';
				$renderer->doc .= 'data-speed="'.$this->dataspeed.'" ';
				$renderer->doc .= 'data-fx="'.$this->datafx.'" ';
				$renderer->doc .= 'data-timeout="'.$this->datatimeout.'">';
                break;
              case DOKU_LEXER_UNMATCHED :  
                $renderer->doc .= $renderer->_xmlEntities($match);
                break;
              case DOKU_LEXER_EXIT :       
				$images = $this->_getNsImages($this->namespace);
				$renderer->doc .= $images;
                $renderer->doc .= '</div>'; 
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
			$value = ltrim(str_replace(['=', "'", '"'], '', $value));
            
            //grab the text before the next space
            $pos = strpos($value, " ");
            if ($pos > 0) {
                $value = substr($value,0,$pos);
            }
            
            $retVal = $value;
        }
        return $retVal;
    }
	function _getNsImages($ns) {
		global $conf;
        $files  = array();
		$images = '';
		$target = $conf['target']['media'];
		$relnf  = '';
		if ($conf['relnofollow'] == 1) { $relnf = 'nofollow'; }
		if ($ns == ".") {
			$ns = getNS(cleanID(getID()));
		} elseif ($ns == "") {
			return false;
		}
		$ns     = str_replace(':', '/', $ns);
		$files  = glob($conf['mediadir'].'/'.$ns."/*.{jp*g,png,gif}", GLOB_BRACE);
       	foreach($files as $file) {
			$base  = pathinfo($file, PATHINFO_BASENAME);
			$meta  = new JpegMeta($file);
			$title = $meta->getField('Simple.Title');
			$alt   = $meta->getField('Iptc.Caption');
			$images .= '<figure>';
			$images .= '<figcaption>'.$title.'</figcaption>';
			$images .= '<a href="/_detail/'.$ns.'/'.$base.'" target="'.$target.'" rel ="'.$relnf.' noopener">';
			$images .= '<img class="media" src=" /_media/'.$ns.'/'.$base.'" title="'.$title.'" alt="'.$alt.'" style="width: '.$this->width.'; height: '.$this->height.';" />';
			$images .= '</a>';
			$images .= '<figcaption>'.$alt.'</figcaption>';
			$images .= '</figure>';
       	}
		return $images;
	}
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
