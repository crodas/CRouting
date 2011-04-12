CRouting
========

Yet another URL router/matcher, inspired by Symfony.

I believe I implemented a symfony-like URL routing. In order to keep things faster, instead of dump the rules, it generated PHP code out of it.


TODO:
=====

  * support for /* (so it can match with the base URL and anything else)
  * Add concept of URL separators (single char):

        foo:
            pattern: /{foo}.{ext}
            defaults: {ext: json}
            requirements:
                ext: php|json|xml
        
        --
        /something (match)
        /something.php (match)
        /something. (will fail)

  * Add validation to the URL generation function. Return nice URL if some optional parametter is missing
  * add docblock for methods
  * documentation
  * use substr_compare for simple comparition

