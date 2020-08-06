<?php

class Element 
{
    private $id;
    private $class;
    private $title;
    private $text;
    private $rel;
    private $tag;

    public function __construct(DOMElement $domElement) 
    {
        $this->tag = $domElement->tagName;
        $this->id = $domElement->getAttribute('id');
        $this->class = $domElement->getAttribute('class');
        $this->title = $domElement->getAttribute('title');
        $this->rel = $domElement->getAttribute('rel');
        $this->text = trim($domElement->nodeValue);
    }

    public function getTag() 
    {
        return $this->tag;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function getText()
    {
        return $this->text;
    }
}

interface HtmlComparator
{
    public function applies(Element $original, Element $new) : bool;
    public function getScore() : int;
    public function getApplicableOutput() : string;
}

class TextComparator implements HtmlComparator
{
    public function applies(Element $original, Element $new) : bool
    {
        return $original->getText() == $new->getText();
    }

    public function getScore() : int
    {
        return 30;
    }

    public function getApplicableOutput() : string
    {
        return 'Element has same content as original -> ' . $this->getScore() . ' points.';
    }
}

class TitleComparator implements HtmlComparator
{
    public function applies(Element $original, Element $new) : bool
    {
        return $original->getTitle() == $new->getTitle();
    }

    public function getScore() : int
    {
        return 20;
    }

    public function getApplicableOutput() : string
    {
        return 'Element has same title attribute as original -> ' . $this->getScore() . ' points.';
    }    
}

class CssClassComparator implements HtmlComparator
{
    public function applies(Element $original, Element $new) : bool
    {
        return $original->getClass() == $new->getClass();
    }

    public function getScore() : int
    {
        return 10;
    }

    public function getApplicableOutput() : string
    {
        return 'Element has same class attribute as original -> ' . $this->getScore() . ' points.';
    }    
}

class RelComparator implements HtmlComparator
{
    public function applies(Element $original, Element $new) : bool
    {
        return $original->getRel() == $new->getRel();
    }

    public function getScore() : int
    {
        return 5;
    }

    public function getApplicableOutput() : string
    {
        return 'Element has same rel attribute as original -> ' . $this->getScore() . ' points.';
    }    
}

class SameElementFinder
{

    const MIN_SCORE = 20;

    private function formatPath($path)
    {
        return str_replace('/', ' < ', substr($path, 1));
    }   

    public function find($originalFile, $diffFile, $originalElementId)
    {
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

        $comparatorImplementations = array_filter(
            get_declared_classes(), 
            function ($className) {
                return in_array('HtmlComparator', class_implements($className));
            }
        );

        foreach ($domElementsByTag as $i => $domElement) {
            $element = new Element($domElement);

            echo 'Element ' . ($i + 1) . PHP_EOL;

            $score = 0;

            foreach ($comparatorImplementations as $comparatorImplementation) {
                $comparator = new $comparatorImplementation;
                if ($comparator->applies($originalElement, $element)) {
                    $score += $comparator->getScore();
                    echo $comparator->getApplicableOutput() . PHP_EOL;
                }
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



