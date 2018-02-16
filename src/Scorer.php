<?php

namespace Siteworx\Passwords;
use League\CLImate\CLImate;

/**
 * Class Scorer
 *
 * @package App\Library\Utilities
 */
class Scorer
{

    private const EXCELLENT_SCORE = 120;
    private const VERY_STRONG_SCORE = 100;
    private const STRONG_SCORE = 80;
    private const FAIR_SCORE = 55;
    private const POOR_SCORE = 25;

    private const VERY_POOR = 0;
    private const POOR = 1;
    private const FAIR = 2;
    private const STRONG = 3;
    private const VERY_STRONG = 4;
    private const EXCELLENT = 5;

    private $bias = 0;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $passwordScore = [
        'calculatedData' => [],
        'total' => 0,
        'strength' => [
            'text_value' => '',
            'int_value' => 1
        ]
    ];

    /**
     * Scorer constructor.
     * @param null $p
     */
    public function __construct($p = null)
    {
        $this->password = $p;
    }

    /**
     * @param string $password
     * @param int -5 to 5 range of a bias to apply to passwords
     * @return Scorer
     * @throws \Exception
     */
    public static function score(string $password, int $bias = 0): Scorer
    {
        $scorer = new static($password);
        $scorer->setBias($bias);
        $scorer->scorePassword();

        return $scorer;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->passwordScore;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->passwordScore['total'];
    }

    /**
     * @return string
     */
    public function stringValue(): string
    {
        return $this->passwordScore['strength']['text_value'];
    }

    /**
     * @return int
     */
    public function intValue(): int
    {
        return $this->passwordScore['strength']['int_value'];
    }

    public function isExcellent(): bool
    {
        return $this->passwordScore['strength']['int_value'] === self::EXCELLENT;
    }

    public function isVeryStrong(): bool
    {
        return $this->passwordScore['strength']['int_value'] === self::VERY_STRONG;
    }

    public function isStrong(): bool
    {
        return $this->passwordScore['strength']['int_value'] === self::STRONG;
    }

    public function isFair(): bool
    {
        return $this->passwordScore['strength']['int_value'] === self::FAIR;
    }

    public function isPoor(): bool
    {
        return $this->passwordScore['strength']['int_value'] === self::POOR;
    }

    public function isVeryPoor(): bool
    {
        return $this->passwordScore['strength']['int_value'] === self::VERY_POOR;
    }

    /**
     * @param int $bias
     * @throws \LogicException
     */
    private function setBias(int $bias): void
    {
        if ($bias > 5 || $bias < -5) {
            throw new \LogicException('Bias is a value of -5 to positive 5');
        }
        $this->bias = round(($bias ** 2) / 3 * $bias);
    }

    /**
     * @throws \Exception
     */
    private function scorePassword(): void
    {
        if ($this->password === null) {
            throw new \InvalidArgumentException('Password Data Not Set!');
        }

        $this->checkLength();
        $this->countUpperCase();
        $this->countLowerCase();
        $this->countNumbers();
        $this->countSpecialChars();
        $this->checkNumbersOnly();
        $this->checkLettersOnly();
        $this->checkRepeatingChars();
        $this->checkReusingChars();
        $this->checkConsecUpperCase();
        $this->checkConscLowerCase();
        $this->checkConsecNumbers();
        $this->checkSeqLetters();
        $this->checkSeqNumbers();

        $total = $this->bias;
        foreach ($this->passwordScore['calculatedData'] as $score) {
            $total += $score['value'];
        }
        $this->passwordScore['total'] = $total;
        $this->passwordScore['strength'] = $this->classifyPasswordScore($total);
    }

    /**
     * Returns the basic Very Poor -> Excellent value of the score
     *
     * @param int $score The value of the password score
     * @return array The int value and the text value of the score
     */
    private function classifyPasswordScore($score): array
    {

        if ($score >= self::EXCELLENT_SCORE) {
            return [
                'text_value' => 'Excellent',
                'int_value' => self::EXCELLENT
            ];
        }

        if ($score >= self::VERY_STRONG_SCORE && $score < self::EXCELLENT_SCORE) {
            return [
                'text_value' => 'Very Strong',
                'int_value' => self::VERY_STRONG
            ];
        }

        if ($score >= self::STRONG_SCORE && $score < self::VERY_STRONG_SCORE) {
            return [
                'text_value' => 'Strong',
                'int_value' => self::STRONG
            ];
        }

        if ($score >= self::FAIR_SCORE && $score < self::STRONG_SCORE) {
            return [
                'text_value' => 'Fair',
                'int_value' => self::FAIR
            ];
        }

        if ($score >= self::POOR_SCORE && $score < self::FAIR_SCORE) {
            return [
                'text_value' => 'Poor',
                'int_value' => self::POOR
            ];
        }

        return [
            'text_value' => 'Very Poor',
            'int_value' => self::VERY_POOR
        ];
    }

    /**
     * Checks for letters that are sequential
     * ie ..    abcd <- BAD
     *          bdca <- GOOD
     *  Liner Grading
     *  -2 points per incident
     */
    private function checkSeqLetters(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['seqLetters']['value'] = 0;
        $this->passwordScore['calculatedData']['seqLetters']['count'] = 0;
        $this->passwordScore['calculatedData']['seqLetters']['displayName'] = 'Sequential Letters';
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $lastChar = $this->password[$i - 1];
            if ($char !== '' && !is_numeric($char) && !is_numeric($lastChar)) {
                $diff = \ord($char) - \ord($lastChar);
                if ($diff === 1 || $diff === -1) {
                    $this->passwordScore['calculatedData']['seqLetters']['value'] -= 2;
                    $this->passwordScore['calculatedData']['seqLetters']['count'] += 1;
                }
            }
            $i++;
        }
    }

    /**
     * Checks for numbers that are sequential
     * ie ..    1234 <- BAD
     *          4213 <- GOOD
     *  Liner Grading
     *  -2 points per incident
     */
    private function checkSeqNumbers(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['seqNumbers']['value'] = 0;
        $this->passwordScore['calculatedData']['seqNumbers']['count'] = 0;
        $this->passwordScore['calculatedData']['seqNumbers']['displayName'] = 'Sequential Numbers';
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $lastChar = $this->password[$i - 1];
            if ($char !== '' && is_numeric($char) && is_numeric($lastChar)) {
                $diff = \ord($char) - \ord($lastChar);
                if ($diff === 1 || $diff === -1) {
                    $this->passwordScore['calculatedData']['seqNumbers']['value'] -= 2;
                    $this->passwordScore['calculatedData']['seqNumbers']['count'] += 1;
                }
            }
            $i++;
        }
    }

    /**
     * Checks for consecutive use of numbers
     * ie ...
     *          adg552 <- 55 is BAD
     *          adb526 <- GOOD
     * Liner Grading
     * -2 per incident
     */
    private function checkConsecNumbers(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['conscNumbers']['value'] = 0;
        $this->passwordScore['calculatedData']['conscNumbers']['count'] = 0;
        $this->passwordScore['calculatedData']['conscNumbers']['displayName'] = 'Consecutive Numbers';
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $lastChar = $this->password[$i - 1];
            if (is_numeric($char)) {
                if (is_numeric($lastChar)) {
                    $this->passwordScore['calculatedData']['conscNumbers']['value'] -= 2;
                    $this->passwordScore['calculatedData']['conscNumbers']['count'] += 1;
                }
            }
            $i++;
        }
    }

    /**
     * Checks for consecutive use of lower case
     * ie ...
     *          AdvRffT <- ff is BAD
     *          AdvRf4T <- GOOD
     * Liner Grading
     * -2 per incident
     */
    private function checkConscLowerCase(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['conscLowerCase']['value'] = 0;
        $this->passwordScore['calculatedData']['conscLowerCase']['count'] = 0;
        $this->passwordScore['calculatedData']['conscLowerCase']['displayName'] = 'Consecutive Lower Case';
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $charDec = \ord($char);
            $lastChar = $this->password[$i - 1];
            $charDecLast = \ord($lastChar);
            if (($charDec <= 122 && $charDec >= 97) || ($charDecLast <= 122 && $charDecLast >= 97)) {
                $this->passwordScore['calculatedData']['conscLowerCase']['value'] -= 2;
                $this->passwordScore['calculatedData']['conscLowerCase']['count'] += 1;
            }
            $i++;
        }
    }

    /**
     * Checks for consecutive use of lower case
     * ie ...
     *          AdvRRfT <- RR is BAD
     *          AdvRf4T <- GOOD
     * Liner Grading
     * -2 per incident
     */
    private function checkConsecUpperCase(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['conscUpperCase']['value'] = 0;
        $this->passwordScore['calculatedData']['conscUpperCase']['count'] = 0;
        $this->passwordScore['calculatedData']['conscUpperCase']['displayName'] = 'Consecutive Upper Case';
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $charDec = \ord($char);
            $lastChar = $this->password[$i - 1];
            $charDecLast = \ord($lastChar);
            if (($charDec <= 90 && $charDec >= 65) || ($charDecLast <= 90 && $charDecLast >= 65)) {
                $this->passwordScore['calculatedData']['conscUpperCase']['value'] += -2;
                $this->passwordScore['calculatedData']['conscUpperCase']['count'] += 1;
            }
            $i++;
        }
    }

    /**
     * Checks entire password for chars used twice or more in a row
     * ie ...
     *          AdvRffT <- ff is BAD
     *          AdvRf4T <- GOOD
     * Exponential Grading
     * -1 per incident to the square
     */
    private function checkRepeatingChars(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['repeatingChars']['value'] = 0;
        $this->passwordScore['calculatedData']['repeatingChars']['count'] = 0;
        $this->passwordScore['calculatedData']['repeatingChars']['displayName'] = 'Repeating Characters';
        $iterations = 0;
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $charDec = \ord($char);
            $lastChar = $this->password[$i - 1];
            $charDecLast = \ord($lastChar);
            if ($charDec === $charDecLast) {
                $this->passwordScore['calculatedData']['repeatingChars']['value'] -= (ceil(($iterations ** 2) / 5) + 1);
                $this->passwordScore['calculatedData']['repeatingChars']['count'] += 1;
                $iterations++;
            }
            $i++;
        }
    }

    /**
     * Checks for consecutive RE-use of chars
     * ie ...
     *          AdvRffT <- ff is BAD
     *          AdvRf4T <- GOOD
     * Exponential Grading
     * kla;sdjlfgkd  <- 'd' and 'l' would be docked for being used twice
     */
    private function checkReusingChars(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['reusingChars']['value'] = 0;
        $this->passwordScore['calculatedData']['reusingChars']['count'] = 0;
        $this->passwordScore['calculatedData']['reusingChars']['displayName'] = 'Reusing Characters';
        while ($i <= \strlen($this->password)) {
            foreach (count_chars($this->password, 1) as $i => $val) {
                if ($val > 1) {
                    $this->passwordScore['calculatedData']['reusingChars']['value'] -= $val;
                    $this->passwordScore['calculatedData']['reusingChars']['count'] += 1;
                }
            }
            $i++;
        }
    }

    /**
     * Checks to see if PW is only numbers
     * ie ..
     *      123498774 <- BAD
     *
     *  Flat dock of -10
     */
    private function checkNumbersOnly(): void
    {
        $this->passwordScore['calculatedData']['numbersOnly']['value'] = 0;
        $this->passwordScore['calculatedData']['numbersOnly']['count'] = 'no';
        $this->passwordScore['calculatedData']['numbersOnly']['displayName'] = 'Numbers Only';

        if (is_numeric($this->password)) {
            $this->passwordScore['calculatedData']['numbersOnly']['value'] = -10;
            $this->passwordScore['calculatedData']['numbersOnly']['count'] = 'yes';
        }
    }

    /**
     * Checks to see if PW is only letters upper or lower
     * ie ..
     *      DFsdffwd <- BAD
     *
     *  Flat dock of -10
     */
    private function checkLettersOnly(): void
    {
        $this->passwordScore['calculatedData']['lettersOnly']['value'] = -10;
        $this->passwordScore['calculatedData']['lettersOnly']['count'] = 'yes';
        $this->passwordScore['calculatedData']['lettersOnly']['displayName'] = 'Letters Only';

        $i = 0;
        while ($i <= \strlen($this->password)) {
            if (is_numeric($this->password[$i])) {
                $this->passwordScore['calculatedData']['lettersOnly']['value'] = 0;
                $this->passwordScore['calculatedData']['lettersOnly']['count'] = 'no';
                return;
            }
            $i++;
        }
    }

    private function countUpperCase(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['upperCase']['value'] = 0;
        $this->passwordScore['calculatedData']['upperCase']['count'] = 0;
        $this->passwordScore['calculatedData']['upperCase']['displayName'] = 'Uppercase Letters';
        $iterations = 0;
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $charDec = \ord($char);
            if ($charDec <= 90 && $charDec >= 65) {
                $this->passwordScore['calculatedData']['upperCase']['value'] += ceil(8 * (.6 ** $iterations));
                $this->passwordScore['calculatedData']['upperCase']['count'] += 1;
                $iterations++;
            }
            $i++;
        }
    }

    /**
     * Counts and scores lower case
     *
     * Scored by and exponential decay
     * starts at 3 points per char and goes down by .7 to the iterations
     */
    private function countLowerCase(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['lowerCase']['value'] = 0;
        $this->passwordScore['calculatedData']['lowerCase']['count'] = 0;
        $this->passwordScore['calculatedData']['lowerCase']['displayName'] = 'Lowercase Letters';
        $iterations = 0;
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $charDec = \ord($char);
            if ($charDec <= 122 && $charDec >= 97) {
                $this->passwordScore['calculatedData']['lowerCase']['value'] += ceil(3 * (.7 ** $iterations));
                $this->passwordScore['calculatedData']['lowerCase']['count'] += 1;
                $iterations++;
            }
            $i++;
        }
    }

    private function countNumbers(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['numbers']['value'] = 0;
        $this->passwordScore['calculatedData']['numbers']['count'] = 0;
        $this->passwordScore['calculatedData']['numbers']['displayName'] = 'Numbers';
        $iterations = 0;
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            if (is_numeric($char)) {
                $this->passwordScore['calculatedData']['numbers']['value'] += ceil(8 * (.5 ** $iterations));
                $this->passwordScore['calculatedData']['numbers']['count'] += 1;
                $iterations++;
            }
            $i++;
        }
    }

    /**
     * Counts and scores lower case
     *
     * Scored by and exponential decay
     * starts at 13 points per char and goes down by .5^iterations
     */
    private function countSpecialChars(): void
    {
        $i = 0;
        $this->passwordScore['calculatedData']['specialChars']['value'] = 0;
        $this->passwordScore['calculatedData']['specialChars']['count'] = 0;
        $this->passwordScore['calculatedData']['specialChars']['displayName'] = 'Special Characters';
        $iterations = 0;
        while ($i <= \strlen($this->password)) {
            $char = $this->password[$i];
            $charDec = \ord($char);
            if (($charDec <= 47 && $charDec >= 33) || ($charDec <= 96 && $charDec >= 91) || ($charDec <= 126 && $charDec >= 123)) {
                $this->passwordScore['calculatedData']['specialChars']['value'] += ceil(13 * (.5 ** $iterations));
                $this->passwordScore['calculatedData']['specialChars']['count'] += 1;
                $iterations++;
            }
            $i++;
        }
    }

    /**
     * Scores password length
     *
     * Passwords < the 8 are automatically docked -15 and scored on a lower scale
     *
     * Liner scale +2 for < 8
     *              +4 for >= 8
     */
    private function checkLength(): void
    {
        $this->passwordScore['calculatedData']['length']['value'] = 0;
        $this->passwordScore['calculatedData']['length']['count'] = 0;
        $this->passwordScore['calculatedData']['length']['displayName'] = 'Length';
        if (\strlen($this->password) < 8) {
            $this->passwordScore['calculatedData']['length']['value'] = -15;
            $i = 0;
            while ($i < \strlen($this->password)) {
                $this->passwordScore['calculatedData']['length']['value'] += 2;
                $this->passwordScore['calculatedData']['length']['count'] += 1;
                $i++;
            }
        } else {
            $this->passwordScore['calculatedData']['length']['value'] = 0;
            $i = 0;
            while ($i < \strlen($this->password)) {
                $this->passwordScore['calculatedData']['length']['value'] += 4;
                $this->passwordScore['calculatedData']['length']['count'] += 1;
                $i++;
            }
        }
    }

}
