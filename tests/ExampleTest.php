<?php

class ExampleTest extends PHPUnit_Framework_TestCase {

  public function testCanInstantiate() {
    $vex = new VerbalExpressions\VerbalExpression();

    $this->assertInstanceOf('VerbalExpressions\\VerbalExpression', $vex);
  }

}
