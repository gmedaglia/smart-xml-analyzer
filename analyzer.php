<?php

class Element {
    private $id;
    private $class;
    private $title;
    private $text;
    private $rel;
    private $tag;

    public function __construct(DOMElement $domElement) {
        $this->tag = $domElement->tagName;
        $this->id = $domElement->getAttribute('id');
        $this->class = $domElement->getAttribute('class');
        $this->title = $domElement->getAttribute('title');
        $this->rel = $domElement->getAttribute('rel');
        $this->text = trim($domElement->nodeValue);
    }

    public function getTag() {
        return $this->tag;
    }

    public function getId() {
        return $this->id;
    }

    public function getClass() {
        return $this->class;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getRel() {
        return $this->rel;
    }

    public function getText() {
        return $this->text;
    }
}

class SameElementFinder {

    const MIN_SCORE = 20;
    const SCORE_TEXT = 30;
    const SCORE_TITLE = 20;
    const SCORE_CLASS = 10;
    const SCORE_REL = 5;

    private function formatPath($path) {
        return str_replace('/', ' < ', substr($path, 1));
    }

    public function find($originalFile, $diffFile, $originalElementId) {
        $dom = new DOMDocument('1.0');

        @$dom->loadHTMLFile($originalFile);

        $originalElement = new Element($dom->getElementById($originalElementId));

        @$dom->loadHTMLFile($diffFile);

        $domElementWithSameIdAsOriginal = $dom->getElementById($originalElementId);

        if (! empty($domElementWithSameIdAsOriginal)) {
            echo 'Found element with same id as original.' . PHP_EOL;
            return $this->formatPath($domElementWithSameIdAsOriginal->getNodePath());
        }

        echo 'Could not find element with same id as original.' . PHP_EOL;

        $domElementsByTag = $dom->getElementsByTagName($originalElement->getTag());

        echo 'Found ' . count($domElementsByTag) . ' elements for tag ' . $originalElement->getTag() . '.' . PHP_EOL;

        $maxScore = 0;
        $bestMatch = null;

        foreach ($domElementsByTag as $i => $domElement) {
            $element = new Element($domElement);

            echo 'Element ' . ($i + 1) . PHP_EOL;

            $score = 0;

            if ($element->getText() == $originalElement->getText()) {
                $score += self::SCORE_TEXT;
                echo 'Element has same content as original -> ' . self::SCORE_TEXT . ' points' . PHP_EOL;
            }
            if ($element->getTitle() == $originalElement->getTitle()) {
                echo 'Element has same title attribute as original -> ' . self::SCORE_TITLE . ' points' . PHP_EOL;
                $score += self::SCORE_TITLE;
            } 
            if ($element->getClass() == $originalElement->getClass()) {
                echo 'Element has same class attribute as original -> ' . self::SCORE_CLASS . ' points' . PHP_EOL;
                $score += self::SCORE_CLASS;
            }
            if ($element->getRel() == $originalElement->getRel()) {
                echo 'Element has same rel attribute as original -> ' . self::SCORE_REL . ' points' . PHP_EOL;
                $score += self::SCORE_CLASS;
            }   

            echo 'Total score: ' . $score . PHP_EOL;

            if ($score > $maxScore) {
                $maxScore = $score;
                $bestMatch = $domElement->getNodePath();
                echo 'Element is best match so far!' . PHP_EOL;
            }

            echo '********************' . PHP_EOL;
        }

        return ($maxScore >= self::MIN_SCORE) ? $this->formatPath($bestMatch) . '.' : 'No matching element found.';
    }

}


$elementId = isset($argv[3]) ? $argv[3] : 'make-everything-ok-button';

$finder = new SameElementFinder;
$result = $finder->find($argv[1], $argv[2], $elementId);

echo 'Result: ' . $result .  PHP_EOL;



