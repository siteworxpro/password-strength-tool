## Password Strength Tester

[![Build Status](https://travis-ci.org/siteworxpro/password-strength-tool.svg?branch=master)](https://travis-ci.org/siteworxpro/password-strength-tool)

A simple and fast password strength tester inspired by WolframAlpha's Calculation

**Usage**

```php
$scorer = \Siteworx\Passwords\Scorer::score('SuperPassword!');

echo json_encode($scorer->toArray());

```

will output and is great for an ajax endpoint

```json
{
  "calculatedData": {
    "length": {
      "value": 56,
      "count": 14,
      "displayName": "Length"
    },
    "upperCase": {
      "value": 13,
      "count": 2,
      "displayName": "Uppercase Letters"
    },
    "lowerCase": {
      "value": 17,
      "count": 11,
      "displayName": "Lowercase Letters"
    },
    "numbers": {
      "value": 0,
      "count": 0,
      "displayName": "Numbers"
    },
    "specialChars": {
      "value": 13,
      "count": 1,
      "displayName": "Special Characters"
    },
    "numbersOnly": {
      "value": 0,
      "count": "no",
      "displayName": "Numbers Only"
    },
    "lettersOnly": {
      "value": -10,
      "count": "yes",
      "displayName": "Letters Only"
    },
    "repeatingChars": {
      "value": -1,
      "count": 1,
      "displayName": "Repeating Characters"
    },
    "reusingChars": {
      "value": -4,
      "count": 2,
      "displayName": "Reusing Characters"
    },
    "conscUpperCase": {
      "value": -8,
      "count": 4,
      "displayName": "Consecutive Upper Case"
    },
    "conscLowerCase": {
      "value": -26,
      "count": 13,
      "displayName": "Consecutive Lower Case"
    },
    "conscNumbers": {
      "value": 0,
      "count": 0,
      "displayName": "Consecutive Numbers"
    },
    "seqLetters": {
      "value": 0,
      "count": 0,
      "displayName": "Sequential Letters"
    },
    "seqNumbers": {
      "value": 0,
      "count": 0,
      "displayName": "Sequential Numbers"
    }
  },
  "total": 50,
  "strength": {
    "text_value": "Poor",
    "int_value": 1
  }
}
```

You can also access several helper methods

```php
if ($scorer->isExcellent()) {
    echo 'Your password is excellent :)';
}
if ($scorer->isPoor()) {
    echo 'Your password is poor :(';
}
```

or

```php
echo 'Your Password is ' . $scorer->stringValue();
```
```
Your Password is Poor
```

**Bias**

You can also provide a bias to the scorer if you want your passwords to be scored higher or lower.

Pass in a value between -5 to 5.

```php
$scorer = \Siteworx\Passwords\Scorer::score('N0wAStrongPassword!', -5);
echo 'This password is ' . $scorer->stringValue();
$scorer = \Siteworx\Passwords\Scorer::score('N0wAStrongPassword!', 0);
echo 'This password is ' . $scorer->stringValue();
$scorer = \Siteworx\Passwords\Scorer::score('N0wAStrongPassword!', 5);
echo 'This password is ' . $scorer->stringValue();

```
```
This password is Poor
This password is Fair
This password is Very Strong
```