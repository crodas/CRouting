CRouting
========

Yet another URL router/matcher, inspired by Symfony.

I believe I implemented a symfony-like URL routing. In order to keep things faster, instead of dump the rules, it generated PHP code out of it.


TODO:
=====

  * support for /* (so it can match with the base URL and anything else)
  * more tests
  * add array-to-url function
  * add docblock for methods
  * documentation
  * use substr_compare for simple comparition
