<?php

namespace CodingLiki\QueryBuilder\Expression;

class Expression
{
    /** @var array<string,array<Expression>> */
    private array $namedChildren = [];
    /** @var array<string,string> */
    private array $childrenPrefixes = [];
    /** @var array<string,string> */
    private array $childrenPostfixes = [];
    /** @var array<string,string> */
    private array $childrenNameSeparators = [];
    /** @var array<string,string> */
    private array $childrenVoidNames = [];
    /** @var array<string,string> */
    private array $childrenSeparators = [];

    public function __construct(
        public string $name = '',
        public string $prefix = '',
        public string $childrenPrefix = '',
        public string $postfix = '',
    )
    {
    }

    public function getRaw(): string
    {
        $childrenString = $this->buildNamedChildrenString();

        return $this->prefix . $this->name . $this->childrenPrefix . $childrenString . $this->postfix;
    }

    public function addChildren(string $name, Expression|array $children): static
    {
        if (!isset($this->namedChildren[$name])) {
            $this->namedChildren[$name] = [];
        }
        $children = is_array($children) ? $children : [$children];

        array_push($this->namedChildren[$name], ...$children);

        return $this;
    }

    public function setChildrenParams(
        string  $name,
        ?string $separator = null,
        ?string $prefix = null,
        ?string $nameSeparator = null,
        ?string $postfix = null,
        ?bool   $voidName = null
    ): static
    {
        $separator === null ?: $this->childrenSeparators[$name] = $separator;
        $prefix === null ?: $this->childrenPrefixes[$name] = $prefix;
        $nameSeparator === null ?: $this->childrenNameSeparators[$name] = $nameSeparator;
        $postfix === null ?: $this->childrenPostfixes[$name] = $postfix;
        $voidName === null ?: $this->childrenVoidNames[$name] = $voidName;

        return $this;
    }

    public function reorderChildren(array $order): static
    {
        $oldChildren = $this->namedChildren;
        $this->namedChildren = [];
        foreach ($order as $name) {
            if (!empty($oldChildren[$name])) {
                $this->namedChildren[$name] = $oldChildren[$name];
            }
        }
        return $this;
    }

    private function buildNamedChildrenString(): string
    {
        $namedChildrenString = '';
        foreach ($this->namedChildren as $name => $children) {
            $childrenString = $this->buildChildrenString($name, $children);

            $prefix = $this->childrenPrefixes[$name] ?? '';
            $postfix = $this->childrenPostfixes[$name] ?? '';
            $nameSeparator = $this->childrenNameSeparators[$name] ?? '';
            $voidName = $this->childrenVoidNames[$name] ?? false;

            $namedChildrenString .= $prefix . ($voidName ? '' : $name) . $nameSeparator . $childrenString . $postfix;
        }
        return $namedChildrenString;
    }

    /**
     * @param string $name
     * @param Expression[] $children
     * @return string
     */
    private function buildChildrenString(string $name, array $children): string
    {
        $childrenStrings = [];

        foreach ($children as $child) {
            $childrenStrings[] = $child->getRaw();
        }

        $separator = $this->childrenSeparators[$name] ?? '';

        return implode($separator, $childrenStrings);
    }
}