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

    /**
     *  @dataProvider routesProvider
     */
    public function testSimpleCRouting($url, $expected)
    {
        $route = new CRouting('route_simple.yml', './tmp/');
        $this->assertEquals($expected, $route->match($url));
    }

    /**
     *  @dataProvider dblroutesProvider
     */
    public function testDoubleSlashes($url, $expected)
    {
        $route = new CRouting('route_simple.yml', './tmp/');
        $this->assertTrue(is_int(strpos($url, '//')));
        $this->assertEquals($expected, $route->match($url));
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
        return array('foobar' => array('pattern' => '/', 'defaults' => array('foo' => 1)));
    }

    public function __invalid($str)
    {
        $this->assertEquals($str, __FILE__);
    }

    public function ztestSimpleGeneration()
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

    public function testInvalidToken() 
    {
        $this->setExpectedException('CRouting_Exception');
        new CRouting_Token('foo', 'bar', 1);
    }

    public function testMissingName() 
    {
        $this->setExpectedException('CRouting_Exception');
        new CRouting_URL(array('pattern' => 'bar'));
    }

    public function testMissingPattern() 
    {
        $this->setExpectedException('CRouting_Exception');
        new CRouting_URL(array('name' => 'bar'));
    }

    public function dblroutesProvider()
    {
        $params = array();
        foreach ($this->routesProvider() as $param) {
            $param[0] = str_replace('/', '//', $param[0]);
            $params[] = $param;
        }
        return $params;
    }

    public function routesProvider()
    {
        $routes = array(
            '/' => array('controller' => 'foo', 'action' => 'bar'),
            '/bar' => array('controller' => 'bar', 'action' => 'index'),
            '/bar/' => array('controller' => 'bar', 'action' => 'index'),
            '/foo/bar' => array('controller' => 'foo', 'action' => 'bar'),
            '/post/1' => array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => '', 'page' => 0),
            '/post/1-foo-bar-bar-foo' => array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'foo-bar-bar-foo', 'page' => 0),
            '/post/1-foo-bar-bar-foo/99' => array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'foo-bar-bar-foo', 'page' => 99),
            '/post/1-c/99' => array('controller' => 'news', 'action' => 'index', 'id' => 1, 'slug' => 'c', 'page' => 99),
            '/history/year/2011' => array('controller' => 'news', 'action' => 'history', 'year' => 2011, 'page' => 0),
            '/history/year/2010/1' => array('controller' => 'news', 'action' => 'history', 'year' => 2010, 'page' => 1),
            '/history/year/2009' => array('controller' => 'news', 'action' => 'history', 'year' => 2009, 'page' => 0),
            '/y/x/ab.' => array('controller' => 'news', 'action' => 'history', 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'),
            '/y/x/ab' => array('controller' => 'news', 'action' => 'history', 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'),
            '/y/2/x/ab' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'),
            '/y/2/x/ab.' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'php'),
            '/y/2/x/ab.xml' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 5, 'six' => 6, 'ext' => 'xml'),
            '/y/2/x/a99b.xml' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 4, 'five' => 99, 'six' => 6, 'ext' => 'xml'),
            '/y/2/x/88a99b.xml' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 6, 'ext' => 'xml'),
            '/y/2/x/88a99b00.xml' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'xml'),
            '/y/2/x/88a99b00.json' => array('controller' => 'news', 'action' => 'history', 'three' => 2, 'four' => 88, 'five' => 99, 'six' => 00, 'ext' => 'json'),
            '/page/99' => array('controller' => 'page', 'action' => 'index', 'foo' => 99),
            
            '/rest/archive' => array('controller' => 'rest', 'action' => 'status', 'id' => 'archive', 'format' => 'json'),
            '/rest/archive..' => array('controller' => 'rest', 'action' => 'status', 'id' => 'archive', 'format' => 'json'),
            '/rest/archive.foo.' => array('controller' => 'rest', 'action' => 'foo', 'id' => 'archive', 'format' => 'json'),
            '/rest/archive.foo.php' => array('controller' => 'rest', 'action' => 'foo', 'id' => 'archive', 'format' => 'php'),
            '/{foo}rest' => array('foo' => 'rest'),


            /* errors */
            '/post/1-foo-bar-bar-foo/xx' => false,
            '/post/x-foo-bar-bar-foo/99' => false,
            '/foo/bar/xxx' => false,
            '/history/year/xxx' => false,
            '/history/year/2009/xxx' => false,
            '/y/2/x/88a99b00.asp' => false,
            '/y/2/x/88a99bxx.json' => false,
            '/y/2/x/88a99.88b00.json' => false,
            '/page/x00' => false,

        );

        $params = array();
        foreach ($routes as $route => $match) {
            $params[] = array($route, $match);
        }

        return $params;
    }

    public function tplErrProvider()
    {
        $files = array();
        foreach (glob("error/*.yml") as $file) {
            $files[] = array($file);
        }
        return $files;
    }

    /** 
     * @dataProvider tplErrProvider
     */
    public function testErrors($file)
    {
        $this->setExpectedException('CRouting_Exception');
        $route = new CRouting($file, './tmp/');
    }

}
