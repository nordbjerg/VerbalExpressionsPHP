# VerbalExpressionsPHP

A PHP adaptation of https://github.com/jehna/VerbalExpressions

## Examples

Note: These examples are only examples. They may not work in all situations.

**Match an URL address**

```
$regex = Vex::start()
	->then('http')
	->maybe('s')
	->then('://')
	->maybe('www.')
	->anythingBut(' ')
	->withAnyCase()
	->end()
	->match('http://www.google.dk'); // bool (true)
```

The fun never stops!