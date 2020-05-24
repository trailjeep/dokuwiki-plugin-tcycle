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
	function connectTo($mode) {
		$this->Lexer->addEntryPattern('<tcycle.*?>(?=.*?</tcycle>)',$mode,'plugin_tcycle');
		$this->Lexer->addPattern('\{\{.*\}\}','plugin_tcycle');
	}
	function postConnect() { $this->Lexer->addExitPattern('</tcycle>','plugin_tcycle'); }
  
    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state) {
            case DOKU_LEXER_ENTER:
                $attributes  = strtolower(substr($match, 5, -1));
                $dataspeed   = $this->_getAttribute($attributes, "speed", $this->getConf('speed'));
                $datafx      = $this->_getAttribute($attributes, "fx", $this->getConf('fx'));
				if (!in_array($datafx, array('scroll', 'fade'))) { $datafx = $this->getConf('fx'); }
                $datatimeout = $this->_getAttribute($attributes, "timeout", $this->getConf('timeout'));
				$width       = $this->_getAttribute($attributes, "width", $this->getConf('width'));
				$height      = $this->_getAttribute($attributes, "height", $this->getConf('height'));
                $namespace   = $this->_getAttribute($attributes, "namespace", $this->getConf('namespace')); 
				if ($namespace === 1) {
					$namespace = str_replace(':', '/', getNS(cleanID(getID())));
				} elseif ($namespace !== 0) {
					$namespace = str_replace(':', '/', $namespace);
				}
				$metadata    = $this->_getAttribute($attributes, "metadata", $this->getConf('metadata'));
				$objectfit   = $this->_getAttribute($attributes, "fit", $this->getConf('fit'));
				if (!in_array($objectfit, array('fill','contain','cover','scale-down','none'))) { $objectfit = $this->getConf('fit'); }
                return array($state, array($dataspeed, $datafx, $datatimeout, $width, $height, $namespace, $metadata, $objectfit));
			case DOKU_LEXER_MATCHED:
				global $conf;
				$addimgs = trim($match);
				$addimgs = preg_replace('/\{\{:?/', $conf['mediadir'].'/', $addimgs);
				$addimgs = preg_replace('/\?.*\}\}/', '', $addimgs);
				$addimgs = preg_replace('/\|.*?\}\}/', '', $addimgs);
				$addimgs = str_replace(':', '/', $addimgs);
				$addimgs = preg_split('/\s+/', $addimgs);
				return array($state, array($addimgs));
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
                list($this->dataspeed, $this->datafx, $this->datatimeout, $this->width, $this->height, $this->namespace, $this->metadata, $this->objectfit) = $match;
				$renderer->doc .= '<div class="tcycle" style="width: '.$this->width.';"';
				$renderer->doc .= 'data-speed="'.$this->dataspeed.'" ';
				$renderer->doc .= 'data-fx="'.$this->datafx.'" ';
				$renderer->doc .= 'data-timeout="'.$this->datatimeout.'">';
                break;
			  case DOKU_LEXER_MATCHED:
				list($this->addimgs) = $match;
				break;
              case DOKU_LEXER_UNMATCHED :  
                $renderer->doc .= $renderer->_xmlEntities($match);
                break;
              case DOKU_LEXER_EXIT :       
				$images = $this->_getImages($this->namespace, $this->addimgs);
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
            
			$retVal = hsc($value);
        }
        return $retVal;
    }
	function _getImages($namespace, $addimgs) {
		global $conf;
        $files  = array();
		$images = '';
		$target = $conf['target']['media'];
		$relnf  = '';
		if ($conf['relnofollow'] == 1) { $relnf = 'nofollow'; }
		if ($namespace !== 0) { $files  = glob($conf['mediadir'].'/'.$namespace."/*.{jp*g,png,gif}", GLOB_BRACE); }
		$files = array_merge((array)$files, (array)$addimgs);
		foreach($files as $file) {
			if (!is_file($file)) { break; }
			$detail = str_replace($conf['mediadir'], '/_detail', $file);
			$media  = str_replace($conf['mediadir'], '/_media', $file);
			$meta  = new JpegMeta($file);
			$title = $meta->getField('Simple.Title');
			if ($title === "") { $title = " "; }
			$alt   = $meta->getField('Iptc.Caption');
			if ($alt === "") { $alt = " "; }
			$images .= '<figure>';
			if ($this->metadata === 1) { $images .= '<figcaption>'.$title.'</figcaption>'; }
			$images .= '<a href="'.$detail.'" target="'.$target.'" rel ="'.$relnf.' noopener">';
			$images .= '<img class="media" src="'.$media.'" title="'.$title.'" alt="'.$alt.'" style="object-fit: '.$this->objectfit.'; width: '.$this->width.'; height: '.$this->height.';" />';
			$images .= '</a>';
			if ( $this->metadata === 1 ) { $images .= '<figcaption>'.$alt.'</figcaption>'; }
			$images .= '</figure>';
       	}
		return $images;
	}
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
