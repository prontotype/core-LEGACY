Feature: extend-include
    As a prototype editor
    I benefit from Twig include mechanisms
    To modularize my prototype

Scenario: include prototype component
    Given I am on "http://prontotype-foo.lo/include"
    Then the ".test-include" element should contain "content included"

