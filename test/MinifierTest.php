<?php 
  require_once(dirname(dirname(__FILE__))."/CssMini.php");
  require_once(dirname(dirname(__FILE__))."/JsMini.php");
  // Test the testing class itself
  Test::is("'yes' is true", 'yes', true);
  Test::not("1 is not false", 1, false);
  Test::identical("true is identical to true", true, true);
  Test::true("1 is true", 1);



  function testAdd(){
    CssMini::resetList();
    CssMini::add('sample.css');
    $list = CssMini::getList();
    return in_array('sample.css', $list);
  }
  Test::true("Add adds the file",testAdd());


  function testResetList(){
    CssMini::add('sample.css');
    CssMini::resetList();
    $list = CssMini::getList();
    return is_array($list) && empty($list);
  }
  Test::true("ResetList erases the list",testResetList());


  function testAddPriority(){
    CssMini::resetList();
    CssMini::add('sample10.css',10);
    CssMini::add('sample1.css',1);
    CssMini::add('sample20.css',20);
    $css_list = CssMini::getList();
    return    array_search('sample10.css', $css_list) == 1 
           && array_search('sample1.css', $css_list) == 0 
           && array_search('sample20.css', $css_list) == 2;
  }
  Test::true("Add respects priority",testAddPriority());


  function testAddUnique(){
    CssMini::resetList();
    CssMini::add('sample.css',10);
    CssMini::add('sample.css',20);
    CssMini::add('sample2.css',25);
    CssMini::add('sample.css',30);
    $css_list = CssMini::getList();
    return    array_search('sample.css', $css_list) == 0 
           && array_search('sample2.css', $css_list) >= 1;
  }
  Test::true("AddUnique keeps just the first occurrence",testAddUnique());

  function testRenderNoMinify(){
    CssMini::resetList();
    CssMini::add('sample.css',10);
    CssMini::add('sample.css',20);
    CssMini::add('sample2.css',25);
    CssMini::add('sample.css',30);
    CssMini::minifyIf(FALSE);
    $tags = CssMini::render();
    return $tags == '<link rel="stylesheet" type="text/css" href="sample.css">'."\n".
                    '<link rel="stylesheet" type="text/css" href="sample2.css">'."\n";
  }
  Test::true("Render tags pointing to original source",testRenderNoMinify());



  function testRenderMinify(){
    CssMini::resetList();
    CssMini::documentRoot(dirname(__FILE__));
    CssMini::add('css/sample.css',10);
    CssMini::add('css/sample.css',20);
    CssMini::add('css/sample2.css',25);
    CssMini::add('css/sample.css',30);
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::cacheDir().'/'.CssMini::minifiedName();
    return $tags == '<link rel="stylesheet" type="text/css" href="'.$minified.'">'."\n";
  }
  Test::true("Render tag pointing to minified source",testRenderMinify());



  function testMinifiedExists(){
    CssMini::resetList();
    CssMini::documentRoot(dirname(__FILE__));
    CssMini::add('css/sample.css');
    CssMini::add('css/sample2.css');
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::minifiedName();

    return file_exists(dirname(__FILE__).'/css/cache/'.$minified);
  }
  Test::true("Render tag pointing to an existing file",testMinifiedExists());



  function testMinifiedContentsOk(){
    CssMini::resetList();
    CssMini::add('css/sample.css');
    CssMini::add('css/sample2.css');
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::minifiedName();
    file_put_contents(dirname(__FILE__).'/css/cache/obtained.css' ,file_get_contents(dirname(__FILE__).'/css/cache/'.$minified));
    return file_get_contents(dirname(__FILE__).'/css/cache/'.$minified) == file_get_contents(dirname(__FILE__).'/css/cache/expected.css');
  }
  Test::true("Cached minified CSS file has correct contents",testMinifiedContentsOk());


  function testClearCache(){
    CssMini::resetList();
    CssMini::add('css/sample2.css');
    CssMini::add('css/sample.css');
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/css/cache/'.$minified);
    CssMini::clearCache();
    $deleted = !file_exists(dirname(__FILE__).'/css/cache/'.$minified);

    return $generated && $deleted; 
  }
  Test::true("ClearCache deletes the CSS cache",testClearCache()); 

  function testAlternativeDocumentRoot(){
    CssMini::documentRoot('./alt_doc_root');
    CssMini::resetList();
    CssMini::add('sample.css');
    CssMini::add('sample2.css');
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/css/cache/'.$minified);
    CssMini::documentRoot('');

    return $generated; 
    ;
  }
  Test::true("Can use alternative DocumentRoot",testAlternativeDocumentRoot());

  function testClearCacheOnAlternativeDocumentRoot(){
    CssMini::documentRoot('./alt_doc_root');
    CssMini::resetList();
    CssMini::add('sample2.css');
    CssMini::add('sample.css');
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/css/cache/'.$minified);
    CssMini::ClearCache();
    $deleted = !file_exists(dirname(__FILE__).'/alt_doc_root/css/cache/'.$minified);
    CssMini::documentRoot('');

    return $generated && $deleted; 
    ;
  }
  Test::true("Can clear cache on alternative DocumentRoot",testClearCacheOnAlternativeDocumentRoot());



  function testAlternativeCacheDir(){
    CssMini::documentRoot('./alt_doc_root');
    CssMini::cacheDir('cache');
    CssMini::resetList();
    CssMini::add('sample.css');
    CssMini::add('sample2.css');
    CssMini::minifyIf(TRUE);
    $tags = CssMini::render();
    $minified = CssMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/cache/'.$minified);
    CssMini::documentRoot('');

    return $generated;
  }
  Test::true("Can use alternative DocumentRoot",testAlternativeDocumentRoot());


  function testJsAdd(){
    JsMini::resetList();
    JsMini::add('sample.js');
    $list = JsMini::getList();
    return in_array('sample.js', $list);
  }
  Test::true("Add adds the file",testAdd());


  function testJsResetList(){
    JsMini::add('sample.js');
    JsMini::resetList();
    $list = JsMini::getList();
    return is_array($list) && empty($list);
  }
  Test::true("ResetList erases the list",testJsResetList());


  function testJsAddPriority(){
    JsMini::resetList();
    JsMini::add('sample10.js',10);
    JsMini::add('sample1.js',1);
    JsMini::add('sample20.js',20);
    $list = JsMini::getList();
    return    array_search('sample10.js', $list) == 1 
           && array_search('sample1.js', $list) == 0 
           && array_search('sample20.js', $list) == 2;
  }
  Test::true("Add respects priority",testJsAddPriority());


  function testJsAddUnique(){
    JsMini::resetList();
    JsMini::add('sample.js',10);
    JsMini::add('sample.js',20);
    JsMini::add('sample2.js',25);
    JsMini::add('sample.js',30);
    $list = JsMini::getList();
    return    array_search('sample.js', $list) == 0 
           && array_search('sample2.js', $list) >= 1;
  }
  Test::true("AddUnique keeps just the first occurrence",testJsAddUnique());

  function testJsRenderNoMinify(){
    JsMini::resetList();
    JsMini::add('sample.js',10);
    JsMini::add('sample.js',20);
    JsMini::add('sample2.js',25);
    JsMini::add('sample.js',30);
    JsMini::minifyIf(FALSE);
    $tags = JsMini::render();
    return $tags == '<script src="sample.js"></script>'."\n".
                    '<script src="sample2.js"></script>'."\n";
  }
  Test::true("Render tags pointing to original source",testJsRenderNoMinify());

  function testJsRenderMinify(){
    JsMini::resetList();
    JsMini::documentRoot(dirname(__FILE__));
    JsMini::add('js/sample.js',10);
    JsMini::add('js/sample.js',20);
    JsMini::add('js/sample2.js',25);
    JsMini::add('js/sample.js',30);
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::cacheDir().'/'.JsMini::minifiedName();
    return $tags == '<script src="'.$minified.'"></script>'."\n";
  }
  Test::true("Render tag pointing to minified source",testJsRenderMinify());



  function testJsMinifiedExists(){
    JsMini::resetList();
    JsMini::documentRoot(dirname(__FILE__));
    JsMini::add('js/sample.js');
    JsMini::add('js/sample2.js');
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();

    return file_exists(dirname(__FILE__).'/js/cache/'.$minified);
  }
  Test::true("Render tag pointing to an existing file",testJsMinifiedExists());



  function testJsMinifiedContentsOk(){
    JsMini::resetList();
    JsMini::add('js/sample.js');
    JsMini::add('js/sample2.js');
    JsMini::minifyIf(TRUE);
    JsMini::clearCache();
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();
    file_put_contents(dirname(__FILE__).'/js/cache/obtained.js' ,file_get_contents(dirname(__FILE__).'/js/cache/'.$minified));
    return file_get_contents(dirname(__FILE__).'/js/cache/'.$minified) == file_get_contents(dirname(__FILE__).'/js/cache/expected.js');
  }
  Test::true("Cached minified JS file has correct contents",testJsMinifiedContentsOk());


  function testJsClearCache(){
    JsMini::resetList();
    JsMini::documentRoot(dirname(__FILE__));
    JsMini::add('js/sample.js');
    JsMini::add('js/sample2.js');
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/js/cache/'.$minified);
    JsMini::clearCache();
    $deleted = !file_exists(dirname(__FILE__).'/js/cache/'.$minified);

    return $generated && $deleted; 
  }
  Test::true("ClearCache deletes the JS cache",testJsClearCache());


  function testJsAlternativeDocumentRoot(){
    JsMini::documentRoot('./alt_doc_root');
    JsMini::resetList();
    JsMini::add('sample.js');
    JsMini::add('sample2.js');
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/js/cache/'.$minified);
    JsMini::documentRoot('');

    return $generated; 
    ;
  }
  Test::true("Can use alternative DocumentRoot for Js",testJsAlternativeDocumentRoot());


  function testJsAlternativeDocumentRootClearCache(){
    JsMini::documentRoot('./alt_doc_root');
    JsMini::resetList();
    JsMini::add('js/sample.js');
    JsMini::add('js/sample2.js');
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/js/cache/'.$minified);
    JsMini::clearCache();
    $deleted = !file_exists(dirname(__FILE__).'/alt_doc_root/js/cache/'.$minified);
    JsMini::documentRoot(dirname(__FILE__));

    return $generated && $deleted; 
  }
  Test::true("ClearCache deletes the JS cache in an alternative document root",testJsAlternativeDocumentRootClearCache());


  function testJsAlternativeCacheDir(){
    JsMini::documentRoot('./alt_doc_root');
    JsMini::cacheDir('cache');
    JsMini::resetList();
    JsMini::add('sample.js');
    JsMini::add('sample2.js');
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/cache/'.$minified);
    JsMini::documentRoot('');

    return $generated;
  }
  Test::true("Can use alternative CacheDir for Js",testJsAlternativeCacheDir());


  function testJsAlternativeDocumentRootAndCacheDirClearCache(){
    JsMini::documentRoot('./alt_doc_root');
    JsMini::cacheDir('alt_cache');
    JsMini::resetList();
    JsMini::add('js/sample.js');
    JsMini::add('js/sample2.js');
    JsMini::minifyIf(TRUE);
    $tags = JsMini::render();
    $minified = JsMini::minifiedName();
    $generated = file_exists(dirname(__FILE__).'/alt_doc_root/alt_cache/'.$minified);
    JsMini::clearCache();
    $deleted = !file_exists(dirname(__FILE__).'/alt_doc_root/alt_cache/'.$minified);
    JsMini::documentRoot(dirname(__FILE__));

    return $generated && $deleted; 
  }
  Test::true("ClearCache deletes the JS cache in an alternative document root and cache dir",testJsAlternativeDocumentRootAndCacheDirClearCache());




  Test::totals();












  class Test {
    protected static $passed = 0;
    protected static $failed = 0;
    protected static $last_echoed;

    public static function true($test_name, $result){
      return static::is($test_name, $result, TRUE);
    }
    
    public static function is($test_name, $result, $expected){
      if($result == $expected) {
        static::passed($test_name);
      } else {
        static::failed($test_name);
      }
    }
    
    public static function not($test_name, $result, $expected){
      if($result == $expected) {
        static::failed($test_name);
      } else {
        static::passed($test_name);
      }
    }

    public static function identical($test_name, $result, $expected){
      if($result === $expected) {
        static::passed($test_name);
      } else {
        static::failed($test_name);
      }
    }

    public static function totals(){
      echo "\n";
      echo static::$passed." tests passed.\n";
      echo static::$failed." tests failed.\n";
    }

    private static function failed($test_name){
      echo "\n".$test_name." -> FAILED\n";
      static::$failed++;
    }

    private static function passed($test_name){
      static::character(".");
      static::$passed++;
    }
    
    private static function character($char){
      echo $char; 
      static::$last_echoed = 'char';
    }

    private static function line($msg){
      if(static::$last_echoed == 'char') echo "\n";
      echo $msg."\n"; 
      static::$last_echoed = 'line';
    }
  }
