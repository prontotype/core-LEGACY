Feature: macros
    As a prototype editor
    I can create and use my own macros

Scenario: define and use macro
    Given I am on "http://prontotype-foo.lo/macros"
    Then the ".vcard" element should contain "Johnny Card"
    And the ".vcard" element should contain "example.com/johnny"
