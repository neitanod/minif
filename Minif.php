<?php 
/*
Minif class.
This class allows you to add css files to your pages and serve them minified and concatenated.
*/

abstract class Minif {

  protected static $do_minify = true;
  protected static $files = array();
  protected static $code = '';
  protected static $extension = 'css';
  protected static $document_root = '';
  protected static $cache_dir = 'css/cache';
  protected static $minified_file_name;
  protected static $clear_cache_chances = 0.001; // automatically clear cache once every 1000 runs approx. 
  protected static $joiner = ";\n";

  public static function add($file, $priority = 10){
    if(!isset(static::$files[$priority]))  static::$files[$priority] = array();
    array_push(static::$files[$priority], $file);
  }

  public static function resetList(){
    static::$files = array(); 
  }

  public static function getList(){
    ksort(static::$files);
    $merged = array();
    foreach(static::$files as $priority){
      $merged = array_merge($merged, $priority);
    }
    return array_unique($merged);
  }

  public static function minifyIf($bool){
    static::$do_minify = !!$bool;
  }

  public static function render(){
  }
 
  public static function minifiedName(){
    $str = '';
    foreach(static::getList() as $file){
      $full_filename = static::documentRoot().'/'.$file;
      if(is_readable($full_filename)){
        $filesize = filesize($full_filename);
        $filemtime = filemtime($full_filename);
      } else {
        $filesize = "x";
        $filemtime = "x";
      }
      $str = $str . $file .'-'. 
            $filesize .'-'. 
            $filemtime . "|";
    }
    return md5($str).'.cachemini.'.static::$extension;
  }

  protected static function minify($code){
  }

  protected static function processCode(){
    static::$code = '';
    foreach(static::getList() as $file){
      $full_filename = static::documentRoot().'/'.$file;
      if(is_readable($full_filename)){
        static::$code = static::$code.
          "/* ".$file." */\n". 
          (strpos($file, '.min.')?
          file_get_contents($full_filename).static::$joiner:
          static::minify(file_get_contents($full_filename)).static::$joiner);
      }
    }
  }

  protected static function cacheFile(){
    return static::documentRoot().'/'.static::cacheDir().'/'.static::minifiedName();
  }


  protected static function saveCache(){
    file_put_contents(static::cacheFile(), static::$code);
  }

  protected static function alreadyCached(){
    return file_exists();
  }

  public static function documentRoot($new = NULL){
    $old = static::$document_root;
    if(!is_null($new)){
      static::$document_root = realpath($new);
    }
    if ($old == "") $old = dirname(__FILE__);
    return $old;
  }

  public static function cacheDir($new = NULL){
    $old = static::$cache_dir;
    if(!is_null($new)){
      static::$cache_dir = $new;
    }
    return $old;
  }

  public static function clearCache(){
    $files = glob(
      static::documentRoot().'/'.
      static::cacheDir().'/*.cachemini.'.static::$extension);
    foreach($files as $file){
      if(is_file($file))
        unlink($file);
    }
  }

  public static function alreadyInCache(){
    return file_exists(static::cacheFile());
  }
}
