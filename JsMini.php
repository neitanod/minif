<?php 
require_once(dirname(__FILE__).'/jsmin.php');
require_once(dirname(__FILE__).'/Minif.php');
/*
JsMinifier class.
This class allows you to add js files to your pages and serve them minified and concatenated.
*/

class JsMini extends Minif{

  protected static $do_minify = true;
  protected static $files = array();
  protected static $code = '';
  protected static $minified_code = '';
  protected static $extension = 'js';
  protected static $document_root = '';
  protected static $cache_dir = 'js/cache';
  protected static $minified_file_name;
  protected static $clear_cache_chances = 0.001; // automatically clear cache once every 1000 runs approx. 

  public static function render(){
    if(static::$do_minify){
      if(!static::alreadyInCache()){
        static::processCode();
        static::saveCache();
      }
      return '<script src="'.static::cacheDir().'/'.static::minifiedName().'"></script>'."\n";
    } else {
      $o = "";
      foreach(static::getList() as $file) {
        $o = $o.'<script src="'.$file.'"></script>'."\n";
      }
      return $o;
    }
  }
 
  protected static function minify($code){
    return JSMin::minify($code);
  }
}
