<?php
$meta['width']    = array('string');
$meta['height']   = array('string');
$meta['speed']    = array('numeric');
$meta['timeout']  = array('numeric');
$meta['fx']       = array('multichoice','_choices' => array('scroll','fade'));
$meta['metadata'] = array('onoff');
$meta['fit']      = array('multichoice','_choices' => array('fill','contain','cover','scale-down','none'));
