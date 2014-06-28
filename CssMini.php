<?php 
require_once(dirname(__FILE__).'/cssmin-v3.0.1-minified.php');
require_once(dirname(__FILE__).'/Minif.php');
/*
CssMinifier class.
This class allows you to add css files to your pages and serve them minified and concatenated.
*/

class CssMini extends Minif{

  protected static $do_minify = true;
  protected static $files = array();
  protected static $code = '';
  protected static $minified_code = '';
  protected static $extension = 'css';
  protected static $document_root = '';
  protected static $cache_dir = 'css/cache';
  protected static $minified_file_name;
  protected static $clear_cache_chances = 0.001; // automatically clear cache once every 1000 runs approx. 
  protected static $joiner = "\n";

  public static function render(){
    if(static::$do_minify){
      if(!static::alreadyInCache()){
        static::processCode();
        static::saveCache();
      }
      return '<link rel="stylesheet" type="text/css" href="'.static::cacheDir().'/'.static::minifiedName().'">'."\n";
    } else {
      $o = "";
      foreach(static::getList() as $file) {
        $o = $o.'<link rel="stylesheet" type="text/css" href="'.$file.'">'."\n";
      }
      return $o;
    }
  }
 
  protected static function minify($code){
    return CssMin::minify($code);
  }
}
