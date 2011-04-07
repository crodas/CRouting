<?php

require "../lib/CRouting.php";

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
    }

    public function testSimpleCRouting()
    {
        $route = new CRouting('route1.yml', './tmp/');
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
        $this->assertEquals($route->match('/y/2/x/ab.'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'));
        $this->assertEquals($route->match('/y/2/x/ab.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/a99b.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 99, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/88a99b.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 6, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/88a99b00.xml'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'xml'));
        $this->assertEquals($route->match('/y/2/x/88a99b00.json'), array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'json'));

        /* error */
        $this->assertEquals($route->match('/post/1-/99'), false);
        $this->assertEquals($route->match('/post/1-foo-bar-bar-foo.xml/xx'), false);
        $this->assertEquals($route->match('/post/x-foo-bar-bar-foo.xml/99'), false);
        $this->assertEquals($route->match('/foo/bar/xxx'), false);
        $this->assertEquals($route->match('/history/year/xxx'), false);
        $this->assertEquals($route->match('/history/year/2009/xxx'), false);
        $this->assertEquals($route->match('/y/2/x/88a99b00.asp'), false);
        $this->assertEquals($route->match('/y/2/x/88a99bxx.json'), false);
    }

    public function testDoubleSlashes()
    {
        $route = new CRouting('route1.yml', './/tmp//');
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

    public function __demo($str)
    {
        $this->assertEquals($str, __FILE__);
        return array(array('pattern' => '/', 'defaults' => array('foo' => 1)));
    }

    public function __invalid($str)
    {
        $this->assertEquals($str, __FILE__);
    }


}
