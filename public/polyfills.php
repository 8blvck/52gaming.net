<?php

function boolint($mixed) {
	return ($mixed===false||$mixed===null||$mixed==='false'||$mixed==='null'||$mixed===0) ? 0 : (is_numeric($mixed) ? intval($mixed) : 1);
}

function exif_format($path) {
	$type = @exif_imagetype($path);
	$formats = [ '.blank', '.gif', '.jpg', '.png', '.swf', '.psd', '.bmp', '.tiff', '.tiff', '.jpc', '.jp2', '.jpx', '.jb2', '.swc', '.iif', '.wbmp', '.xbm', '.ico', '.webp'];
	return $formats[$type] ?? $formats[0];
}

function str_truncate($string, $length = 200) {
    if (strlen($string) > $length) $string = mb_substr($string, 0, $length) . '...';
    return $string;
}

function array_assign($a, $b) {
	foreach($b as $key => $item):
		if(array_key_exists($key, $a)):
			$a[$key] = $b[$key];
		endif;
	endforeach;
	return $a;
}

function str_replace_first($f, $t, $c) {
    return preg_replace('/'.preg_quote($f, '/').'/', $t, $c, 1);
}

function is_url($mixed) {
	return filter_var($mixed, FILTER_VALIDATE_URL);
}

function object_to_array($object) {
	$array = [];
	foreach($object as $item) $array[] = (array)$item;
	return $array;
}

function object_filter($object, $reducer = false) {
	if($object instanceof stdClass or !is_array($object)) $object = object_to_array($object);
	return array_filter($object, $reducer);
}

function object_column($object, $column = false) {
	if($object instanceof stdClass or !is_array($object)) $object = object_to_array($object);
	return array_column($object, $column);
}

function object_map($object, $editor = false) {
	if($object instanceof stdClass or !is_array($object)) $object = object_to_array($object);
	return array_map($editor, $object);
}

function object_find($object, $reducer = false, $editor = false) {
	$result = null;
	foreach($object as $item) {
		if($reducer($item)) {
			$result = $item; break;
		}
	}
	if($result && is_callable($editor)) $result = $editor($result);
	return $result;
}

function object_last($object) {
	if($object instanceof stdClass or !is_array($object)) $object = object_to_array($object);
	return (object)end($object);
}

function object_first($object) {
	if($object instanceof stdClass or !is_array($object)) $object = object_to_array($object);
	return empty($object) ? null : (object)$object[0];
}

function object_morph($object, $reducer = false, $editor = false) {
	$array = object_to_array($object);
	$array = object_filter($array, $reducer);
	$array = object_map($array, $editor);
	return $array;
}
