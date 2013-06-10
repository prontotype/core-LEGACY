Feature: navigation helper
    As a prototype editor
    I can generate a navigation for my prototype structure

Scenario Outline: default navigation
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#navigation-default" element should contain "<word>"

    Examples:
        | word |
        | data |
        | extend |
        | images |
        | include |
        | sub |
        | another |
        | overview |

Scenario: navigation id
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#navigation-id" element should contain "navid"

Scenario: navigation without overview
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#navigation-hide-index" element should not contain "overview"

Scenario Outline: navigation of depth 1
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#navigation-depth-1" element should contain "<word>"

    Examples:
        | word |
        | data |
        | extend |
        | images |
        | include |
        | home    |

Scenario Outline: navigation of depth 1
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#navigation-depth-1" element should not contain "<word>"

    Examples:
        | word |
        | another |
        | overview |

Scenario Outline: navigation start from sub
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#navigation-sub" element should contain "<word>"

    Examples:
        | word |
        | yet |
        | another |
        | page |
        | overview |

Scenario Outline: default sitemap
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#sitemap-default" element should contain "<word>"

    Examples:
        | word |
        | data |
        | extend |
        | images |
        | include |
        | sub |
        | another |
        | overview |

Scenario: sitemap id
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#sitemap-id" element should contain "navid"

Scenario: sitemap without overview
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#sitemap-hide-index" element should not contain "overview"

Scenario Outline: sitemap of depth 1
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#sitemap-depth-1" element should contain "<word>"

    Examples:
        | word |
        | data |
        | extend |
        | images |
        | include |
        | home    |

Scenario Outline: sitemap of depth 1
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#sitemap-depth-1" element should not contain "<word>"

    Examples:
        | word |
        | another |
        | overview |

Scenario Outline: sitemap start from sub
    Given I am on "http://prontotype-foo.lo/navigation"
    Then the "#sitemap-sub" element should contain "<word>"

    Examples:
        | word |
        | yet |
        | another |
        | page |
        | overview |
