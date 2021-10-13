<?php
namespace CodingLiki\QueryBuilder\Expression;

class RuleExpressionBuilder
{
    public static function equalRight(string|Expression $right): Expression
    {
        $right instanceof Expression ?: $right = new Expression($right);

        return (new Expression())
            ->addChildren('=', $right)
            ->setChildrenParams('=', nameSeparator: ' ');
    }

    public static function rule(string|Expression $left, string|Expression $right, string $operation): Expression
    {
        $right instanceof Expression ?: $right = new Expression($right);
        $left instanceof Expression ?: $left = new Expression($left);

        return (new Expression( ))
            ->addChildren('operands', [$left, $right])
            ->setChildrenParams('operands', separator: " $operation ", voidName: true);
    }

    public static function equal(string|Expression $left, string|Expression $right): Expression
    {
        return self::rule($left, $right, '=');
    }

    public static function notEqual(string|Expression $left, string|Expression $right): Expression
    {
        return self::rule($left, $right, '<>');
    }

    public static function less(string|Expression $left, string|Expression $right): Expression
    {
        return self::rule($left, $right, '<');
    }

    public static function lessOrEqual(string|Expression $left, string|Expression $right): Expression
    {
        return self::rule($left, $right, '<=');
    }

    public static function more(string|Expression $left, string|Expression $right): Expression
    {
        return self::rule($left, $right, '>');
    }

    public static function moreOrEqual(string|Expression $left, string|Expression $right): Expression
    {
        return self::rule($left, $right, '>=');
    }

    public static function in(string|Expression $left, string|Expression $in): Expression
    {
        $left instanceof Expression ?: $left = new Expression($left);
        $in instanceof Expression ?: $in = new Expression($in);

        return $left
            ->addChildren('IN', $in)
            ->setChildrenParams('IN', prefix: ' ', nameSeparator: '(', postfix: ')');
    }

    public static function inRight(string|Expression $in): Expression
    {
        $in instanceof Expression ?: $in = new Expression($in);

        return (new Expression())
            ->addChildren('IN', $in)
            ->setChildrenParams('IN', nameSeparator: '(', postfix: ')');
    }

    public static function like(string|Expression $left, string|Expression $like): Expression
    {
        $left instanceof Expression ?: $left = new Expression($left);
        $like instanceof Expression ?: $like = new Expression($like);

        return $left
            ->addChildren('LIKE', $like)
            ->setChildrenParams('LIKE', prefix: ' ',nameSeparator: ' ');
    }

    public static function between(string|Expression $left, string|Expression $start, string|Expression $end): Expression
    {
        $left instanceof Expression ?: $left = new Expression($left);
        $start instanceof Expression ?: $start = new Expression($start);
        $end instanceof Expression ?: $end = new Expression($end);

        return $left
            ->addChildren('BETWEEN', [$start,$end])
            ->setChildrenParams('BETWEEN', separator: ' AND ',prefix: ' ', nameSeparator: ' ');
    }

    public static function not(string|Expression $expression): Expression
    {
        $expression instanceof Expression ?: $expression = new Expression($expression);

        return (new Expression(''))
            ->addChildren('NOT', $expression)
            ->setChildrenParams('NOT', nameSeparator: ' ');
    }
}

