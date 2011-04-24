<?php

require "../lib/CRouting.php";

function mycustom_validator($segment)
{
    return $segment == 'foo';
}

class Validator 
{
    public static function test($segment)
    {
        return $segment == 'bar';
    }
}

class templateTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidArgs() 
    {
        try {
            $route = new CRouting('/foo/bar');
            $this->assertTrue(false);
        } catch (CRouting_Exception $e) {
            $this->assertTrue(true);
        }
        try {
            $route = new CRouting(__FILE__, '/foo/bar /bar');
            $this->assertTrue(false);
        } catch (CRouting_Exception $e) {
            $this->assertTrue(true);
        }
        foreach(glob("tmp/*.php") as $file) {
            unlink($file);
        }
        @mkdir('tmp');
    }

    public function testSimpleCRouting()
    {
        $route = new CRouting('route_simple.yml', './tmp/');
        $this->assertEquals($route->match('/'), array('controller' => 'foo', 'action' => 'bar'));
        $this->assertEquals($route->match('/bar'), array('controller' => 'bar', 'action' => 'index'));
        $this->assertEquals($route->match('/foo/bar'), array('controller' => 'foo', 'action' => 'bar'));
        $this->assertEquals($route->match('/post/1-foo-bar-bar-foo.xml'), array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'foo-bar-bar-foo.xml', 'page' => 0));
        $this->assertEquals($route->match('/post/1-foo-bar-bar-foo.xml/99'), array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'foo-bar-bar-foo.xml', 'page' => 99));
        $this->assertEquals($route->match('/post/1-c/99'), array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'c', 'page' => 99));
        $this->assertEquals($route->match('/history/year/2011'), array('controller' => 'news', 'action' => 'history', 'year' => 2011, 'page' => 0));
        $this->assertEquals($route->match('/history/year/2010/1'), array('controller' => 'news', 'action' => 'history', 'year' => 2010, 'page' => 1));
        $this->assertEquals($route->match('/history/year/2009'), array('controller' => 'news', 'action' => 'history', 'year' => 2009, 'page' => 0));
        $this->assertEquals($route->match('/y/x/ab.'), array('controller' => 'news', 'action' => 'history', 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('/y/x/ab'), array('controller' => 'news', 'action' => 'history', 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('/y/2/x/ab'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('/y/2/x/ab.'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('/y/2/x/ab.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/a99b.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 99, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/88a99b.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/88a99b00.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/88a99b00.json'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'json'));
        $this->assertEquals($route->match('/page/99'), array('controller' => 'page', 'action' => 'index', 'foo' => 99));

        $this->assertEquals($route->match('/rest/archive..'), array('controller' => 'rest', 'action' => 'status', 'id' => 'archive', 'format' => 'json'));
        $this->assertEquals($route->match('/rest/archive.foo.'), array('controller' => 'rest', 'action' => 'foo', 'id' => 'archive', 'format' => 'json'));
        $this->assertEquals($route->match('/rest/archive.foo.php'), array('controller' => 'rest', 'action' => 'foo', 'id' => 'archive', 'format' => 'php'));

        /* error */
        $this->assertEquals($route->match('/post/1-/99'), false);
        $this->assertEquals($route->match('/post/1-foo-bar-bar-foo.xml/xx'), false);
        $this->assertEquals($route->match('/post/x-foo-bar-bar-foo.xml/99'), false);
        $this->assertEquals($route->match('/foo/bar/xxx'), false);
        $this->assertEquals($route->match('/history/year/xxx'), false);
        $this->assertEquals($route->match('/history/year/2009/xxx'), false);
        $this->assertEquals($route->match('/y/2/x/88a99b00.asp'), false);
        $this->assertEquals($route->match('/y/2/x/88a99bxx.json'), false);
        $this->assertEquals($route->match('/y/2/x/88a99.88b00.json'), false);
        $this->assertEquals($route->match('/page/00'), false);
    }

    public function testRequestMethod()
    {
        $route = new CRouting('route_simple.yml', './tmp/');
        $this->assertEquals($route->match('/'), array('controller' => 'foo', 'action' => 'bar'));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals($route->match('/'), array('controller' => 'request', 'action' => 'check'));
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertEquals($route->match('/'), array('controller' => 'request', 'action' => 'check'));
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals($route->match('/'), array('controller' => 'foo', 'action' => 'bar'));
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals($route->match('/get/foo'), array('controller' => 'request', 'action' => 'check'));
    }

    public function testDoubleSlashes()
    {
        $route = new CRouting('route_simple.yml', './/tmp//');
        $this->assertEquals($route->match('//'), array('controller' => 'foo', 'action' => 'bar'));
        $this->assertEquals($route->match('//bar'), array('controller' => 'bar', 'action' => 'index'));
        $this->assertEquals($route->match('//foo//bar'), array('controller' => 'foo', 'action' => 'bar'));
        $this->assertEquals($route->match('//post//1-foo-bar-bar-foo.xml'), array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'foo-bar-bar-foo.xml', 'page' => 0));
        $this->assertEquals($route->match('//post//1-foo-bar-bar-foo.xml//99'), array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'foo-bar-bar-foo.xml', 'page' => 99));
        $this->assertEquals($route->match('//post//1-c//99'), array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'c', 'page' => 99));
        $this->assertEquals($route->match('//history//year//2011'), array('controller' => 'news', 'action' => 'history', 'year' => 2011, 'page' => 0));
        $this->assertEquals($route->match('//history//year//2010//1'), array('controller' => 'news', 'action' => 'history', 'year' => 2010, 'page' => 1));
        $this->assertEquals($route->match('//history//year//2009'), array('controller' => 'news', 'action' => 'history', 'year' => 2009, 'page' => 0));
        $this->assertEquals($route->match('//history//year//2009'), array('controller' => 'news', 'action' => 'history', 'year' => 2009, 'page' => 0));
        $this->assertEquals($route->match('//y//x//ab.'), array('controller' => 'news', 'action' => 'history', 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('//y//2//x//ab.'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('//y//2//x//ab.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('//y//2//x//a99b.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 99, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('//y//2//x//88a99b.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('//y//2//x//88a99b00.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'xml'));
        $this->assertEquals($route->match('//y//2//x//88a99b00.json'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'json'));

        //* error *//
        $this->assertEquals($route->match('//post//1-//99'), false);
        $this->assertEquals($route->match('//post//1-foo-bar-bar-foo.xml//xx'), false);
        $this->assertEquals($route->match('//post//x-foo-bar-bar-foo.xml//99'), false);
        $this->assertEquals($route->match('//foo//bar//xxx'), false);
        $this->assertEquals($route->match('//history//year//xxx'), false);
        $this->assertEquals($route->match('//history//year//2009//xxx'), false);
        $this->assertEquals($route->match('//y//2//x//88a99b00.asp'), false);
        $this->assertEquals($route->match('//y/2/x/88a99bxx.json'), false);
    }

    public function testCustomParser()
    {
        try {
            CRouting::setParser(array($this, 'demo'));
            $this->assertTrue(false);
        } catch (CRouting_Exception $e) {
            $this->assertTrue(true);
        }

        CRouting::setParser(array($this, '__invalid'));
        try {
            $route = new CRouting(__FILE__, './tmp');
            $this->assertTrue(false);
        } catch (CRouting_Exception $e) {
            $this->assertTrue(true);
        }

        CRouting::setParser(array($this, '__demo'));

        $route = new CRouting(__FILE__, './tmp');
        $this->assertEquals($route->match('/'), array('foo' => 1));

        CRouting::setParser(array('sfYaml', 'load'));
    }

    public function testInvalidCallbackValidation()
    {
        try {
            $route = new CRouting('route_callback_invalid.yml', './tmp/');
            $this->assertTrue(false);
        } catch (CRouting_Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testCallbackValidation()
    {
        $route = new CRouting('route_callback.yml', './tmp/');
        $this->assertEquals($route->match('/foo'), array('action' => 'foo', 'page' => 0));
        $this->assertEquals($route->match('/foo/1'), array('action' => 'foo', 'page' => 1));
        $this->assertEquals($route->match('/foo/x'), false);
        $this->assertEquals($route->match('/bar'), array('action' => 'bar', 'page' => 0));
        $this->assertEquals($route->match('/bar/1'), array('action' => 'bar', 'page' => 1));
        $this->assertEquals($route->match('/foo/x'), false);
        $this->assertEquals($route->match('/xfoo'), false);
        $this->assertEquals($route->match('/xfoo/1'), false);
        $this->assertEquals($route->match('/xbar'), false);
        $this->assertEquals($route->match('/xbar/1'), false);
    }

    public function __demo($str)
    {
        $this->assertEquals($str, __FILE__);
        return array('foobar' => array('pattern' => '/', 'defaults' => array('foo' => 1)));
    }

    public function __invalid($str)
    {
        $this->assertEquals($str, __FILE__);
    }

    public function testSimpleGeneration()
    {
        $route = new CRouting('route_simple.yml', './tmp/');
        $this->assertEquals($route->generate('default'), '/');
        $this->assertEquals($route->generate('onlyPostAndDelete'), '/');
        $this->assertEquals($route->generate('checkMethod'), '/get/foo');
        $this->assertEquals($route->generate('index', array('controller' => 'foo')), '/foo/index');
        $this->assertEquals($route->generate('index', array('controller' => 'foo', 'action' => 'bar')), '/foo/bar');
        $this->assertEquals($route->generate('blog_post', array('id' => 1, 'slug' => 'foo-bar')), '/post/1-foo-bar/0');

        $this->assertEquals($route->generate('index', array()), false);
        $this->assertEquals($route->generate('blog_post', array('id' => 1)), false);
    }

    public function testRegex()
    {
        $route = new CRouting('route_regex.yml', './tmp/');
        $this->assertEquals($route->match('/foofoo'), array('action' => 'foofoo', 'page' => 0));
        $this->assertEquals($route->match('/foobarfoo'), array('action' => 'foobarfoo', 'page' => 0));
        $this->assertEquals($route->match('/fooxxxfoo'), array('action' => 'fooxxxfoo', 'page' => 0));
        $this->assertEquals($route->match('/xxxfoo'), array('action' => 'xxxfoo'));
        $this->assertEquals($route->match('/something/xxxfoo'), array('action' => 'xxxfoo'));
        $this->assertEquals($route->match('/something/xxxFOO'), array('action' => 'xxxFOO'));
        $this->assertEquals($route->match('/something/xxxOO9'), false);
    }
}
