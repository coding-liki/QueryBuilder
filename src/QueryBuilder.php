<?php

namespace CodingLiki\QueryBuilder;

use CodingLiki\QueryBuilder\Expression\Expression;
use CodingLiki\QueryBuilder\Expression\RuleExpressionBuilder;

class QueryBuilder
{
    public const LEFT_JOIN = 'LEFT';
    public const INNER_JOIN = 'INNER';
    public const RIGHT_JOIN = 'RIGHT';
    public const JOIN = '';

    public const EXPRESSIONS_ORDER = [
        'selectFields',
        'FROM',
        'UPDATE',
        'INSERT',
        'INTO',
        'table',
        'fields',
        'VALUES',
        'SET',
        'join',
        'WHERE',
        'OR',
        'GROUP BY',
        'HAVING',
        'ORHAVING',
        'ORDER BY',
        'LIMIT',
        'OFFSET',
    ];

    private const DEFAULT_BORDERS = [
        '(',
        ')'
    ];


    private ?Expression $rootExpression;


    public function select(array|string $selectFields, bool $distinct = false): static
    {
        $name = $distinct ? 'SELECT DISTINCT' : 'SELECT';

        $this->rootExpression = (new Expression($name, childrenPrefix: ' '))
            ->setChildrenParams('selectFields', separator: ', ', postfix: ' ', voidName: true);

        if (!is_array($selectFields)) {
            $selectFields = [$selectFields];
        }


        $selectExpressions = [];
        foreach ($selectFields as $as => $field) {
            $toAdd = $field instanceof Expression ? $field : new Expression($field);
            if (is_string($as)) {
                $toAdd
                    ->addChildren('AS', new Expression($as))
                    ->setChildrenParams('AS', prefix: ' ', nameSeparator: ' ');
            }
            $selectExpressions[] = $toAdd;
        }

        $this->rootExpression->addChildren('selectFields', $selectExpressions);

        return $this;
    }

    public function update(string $table, array $setValues): static
    {
        $this->rootExpression = (new Expression('UPDATE', childrenPrefix: ' '))
            ->addChildren('table', new Expression($table))
            ->setChildrenParams('table', voidName: true);

        $setExpressions = [];
        foreach ($setValues as $field => $value) {
            $setExpressions[] = RuleExpressionBuilder::equal($field, $value);

        }
        $this->rootExpression
            ->addChildren('SET', $setExpressions)
            ->setChildrenParams('SET', separator: ', ', prefix: ' ', nameSeparator: ' ');


        return $this;
    }

    public function insert(string $into, array $fields, $values): static
    {
        $this->rootExpression = (new Expression('INSERT', childrenPrefix: ' '))
            ->addChildren('INTO', new Expression($into))
            ->setChildrenParams('INTO', nameSeparator: ' ');

        $fieldsExpression = new Expression(prefix: '(', postfix: ')');

        $fieldExpressions = [];

        foreach ($fields as $field) {
            $fieldExpressions[] = new Expression($field);
        }

        $fieldsExpression
            ->addChildren('fields', $fieldExpressions)
            ->setChildrenParams('fields', separator: ', ', voidName: true);
        $this->rootExpression
            ->addChildren('fields', $fieldsExpression)
            ->setChildrenParams('fields', voidName: true);
        if (is_array($values)) {
            $valueListExpressions = [];
            foreach ($values as $valueList) {
                $valueListExpression = new Expression(prefix: '(', postfix: ')');

                $valueExpressions = [];
                foreach ($valueList as $value) {
                    $valueExpressions[] = $value instanceof Expression ? $value : new Expression($value);
                }

                $valueListExpressions[] = $valueListExpression
                    ->addChildren('values', $valueExpressions)
                    ->setChildrenParams('values', separator: ', ', voidName: true);
            }
            $this->rootExpression
                ->addChildren('VALUES', $valueListExpressions)
                ->setChildrenParams('VALUES', separator: ', ', prefix: ' ');
        } else if ($values instanceof Expression) {
            $this->rootExpression
                ->addChildren('VALUES', $values)
                ->setChildrenParams('VALUES', prefix: ' ', voidName: true);
        }

        return $this;
    }

    public function delete(): static
    {
        $this->rootExpression = new Expression('DELETE', childrenPrefix: ' ');

        return $this;
    }

    public function getAsSubquery(?array $borders = null): Expression
    {
        if ($borders === null || count($borders) !== 2) {
            $borders = self::DEFAULT_BORDERS;
        }
        return (new Expression('', prefix: $borders[0], postfix: $borders[1]))
            ->addChildren('sub_expression', $this->getExpression())
            ->setChildrenParams('sub_expression', voidName: true);
    }

    public function where(array $where): static
    {
        $whereExpressions = [];
        foreach ($where as $field => $rule) {
            if ($rule instanceof Expression) {
                $whereExpressions[] = $rule;
            } else {
                $rule = new Expression($rule, '');
                $whereExpressions[] = (new Expression($field))
                    ->addChildren('rule', $rule)
                    ->setChildrenParams('rule', prefix: ' ', voidName: true);
            }
        }

        $this->rootExpression
            ->addChildren('WHERE', $whereExpressions)
            ->setChildrenParams('WHERE', separator: ' AND ', prefix: ' ', nameSeparator: ' ');

        return $this;
    }

    public function having(array $where): static
    {
        $whereExpressions = [];
        foreach ($where as $field => $rule) {
            if ($rule instanceof Expression) {
                $whereExpressions[] = $rule;
            } else {
                $rule = new Expression($rule, '');
                $whereExpressions[] = (new Expression($field))
                    ->addChildren('rule', $rule)
                    ->setChildrenParams('rule', prefix: ' ', voidName: true);
            }
        }

        $this->rootExpression
            ->addChildren('HAVING', $whereExpressions)
            ->setChildrenParams('HAVING', separator: ' AND ', prefix: ' ', nameSeparator: ' ');

        return $this;
    }

    public function orWhere(array $where): static
    {
        $whereExpressions = [];
        foreach ($where as $field => $rule) {
            if ($rule instanceof Expression) {
                $whereExpressions[] = $rule;
            } else {
                $rule = new Expression($rule, '');
                $whereExpressions[] = (new Expression($field))
                    ->addChildren('rule', $rule)
                    ->setChildrenParams('rule', prefix: ' ', voidName: true);
            }
        }

        $this->rootExpression
            ->addChildren('OR', $whereExpressions)
            ->setChildrenParams('OR', separator: ' OR ', prefix: ' ', nameSeparator: ' ');

        return $this;
    }

    public function orHaving(array $where): static
    {
        $whereExpressions = [];
        foreach ($where as $field => $rule) {
            if ($rule instanceof Expression) {
                $whereExpressions[] = $rule;
            } else {
                $rule = new Expression($rule, '');
                $whereExpressions[] = (new Expression($field))
                    ->addChildren('rule', $rule)
                    ->setChildrenParams('rule', prefix: ' ', voidName: true);
            }
        }

        $this->rootExpression
            ->addChildren('ORHAVING', $whereExpressions)
            ->setChildrenParams('ORHAVING', separator: ' OR ', prefix: ' OR', nameSeparator: ' ', voidName: true);

        return $this;
    }

    public function from(array|string $fromTables): static
    {
        if (!is_array($fromTables)) {
            $fromTables = [$fromTables];
        }

        $fromExpressions = [];
        foreach ($fromTables as $as => $table) {
            $from = $table instanceof Expression ? $table : new Expression($table);
            if (is_string($as)) {
                $from
                    ->addChildren('as', new Expression($as))
                    ->setChildrenParams('as', prefix: ' ', voidName: true);
            }
            $fromExpressions[] = $from;
        }

        $this->rootExpression
            ->addChildren('FROM', $fromExpressions)
            ->setChildrenParams('FROM', separator: ', ', nameSeparator: ' ');

        return $this;
    }

    public function limit(int $limit, ?int $offset = null): static
    {
        $this->rootExpression
            ->addChildren('LIMIT', new Expression($limit))
            ->setChildrenParams('LIMIT', prefix: ' ', nameSeparator: ' ');
        if ($offset !== null) {
            $this->offset($offset);
        }

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->rootExpression
            ->addChildren('OFFSET', new Expression($offset))
            ->setChildrenParams('OFFSET', prefix: ' ', nameSeparator: ' ');

        return $this;
    }

    /**
     * @param array<string, Expression|string> $orderBy
     */
    public function orderBy(array $orderBy): static
    {
        $orderExpressions = []; 
        foreach($orderBy as $name => $orderItem){
            if($orderItem instanceof Expression){
                $orderExpressions[] = $orderItem;
            } else {
                $orderExpressions[] = (new Expression($name, childrenPrefix: ' '))
                    ->addChildren('order', new Expression($orderItem))
                    ->setChildrenParams('order', voidName: true);
            }
        }

        $this->rootExpression
            ->addChildren('ORDER BY', $orderExpressions)
            ->setChildrenParams('ORDER BY', separator: ', ', nameSeparator: ' ',prefix: ' ');

        return $this;        
    }

    /**
     * @param array<string, Expression|string> $orderBy
     */
    public function groupBy(array $groupBy): static
    {
        $groupExpressions = []; 
        foreach($groupBy as $groupItem){
            $groupItem instanceof Expression ?: $groupItem = new Expression($groupItem);
            $groupExpressions[] = $groupItem;
        }

        $this->rootExpression
            ->addChildren('GROUP BY', $groupExpressions)
            ->setChildrenParams('GROUP BY', separator: ', ', nameSeparator: ' ',prefix: ' ');

        return $this;        
    }

    public function join(string|Expression $table, ?string $alias, array $onRules, string $type = self::JOIN): static
    {
        $name = 'JOIN';
        if ($type !== static::JOIN) {
            $name = "$type $name";
        }

        $table = $table instanceof Expression ? $table : new Expression($table);

        $joinExpression = (new Expression($name, childrenPrefix: ' '))
            ->addChildren('table', $table)
            ->setChildrenParams('table', separator: " ", voidName: true);

        if ($alias !== null) {
            $joinExpression->addChildren('table', new Expression($alias));
        }

        $onExpressions = [];
        $onOrExpressions = [];
        $childGroup = 'ON';
        foreach ($onRules as $field => $rule) {
            if (is_array($rule)) {
                $map = [
                    'AND' => 'ON',
                    'OR' => 'OR'
                ];

                if (count($rule) > 1) {
                    $childGroup = $map[strtoupper($rule[0])] ?? 'ON';
                    $rule = $rule[1];
                }
            }
            if(is_string($field)){
                $onExpression = (new Expression($field, childrenPrefix: ' '))
                    ->addChildren('rule', $rule instanceof Expression ? $rule : new Expression($rule))
                    ->setChildrenParams('rule', voidName: true);
            } else {
                $onExpression =  $rule instanceof Expression ? $rule : new Expression($rule);
            }
            switch ($childGroup) {
                case 'ON':
                    $onExpressions[] = $onExpression;
                    break;
                case 'OR':
                    $onOrExpressions[] = $onExpression;
                    break;
            }
        }

        $joinExpression
            ->addChildren('ON', $onExpressions)
            ->addChildren('OR', $onOrExpressions)
            ->setChildrenParams('ON', separator: ' AND ', prefix: ' ', nameSeparator: ' ')
            ->setChildrenParams('OR', separator: ' OR ', prefix: ' ', nameSeparator: ' ')
            ->reorderChildren([
                'table',
                'ON',
                'OR'
            ]);
        $this->rootExpression
            ->addChildren('join', $joinExpression)
            ->setChildrenParams('join', prefix: " ", voidName: true);

        return $this;
    }

    public function getExpression(): Expression
    {
        $this->rootExpression->reorderChildren(self::EXPRESSIONS_ORDER);

        return $this->rootExpression;
    }

    public function getRaw(): string
    {
        $this->rootExpression->reorderChildren(self::EXPRESSIONS_ORDER);

        return $this->rootExpression->getRaw();
    }

}

