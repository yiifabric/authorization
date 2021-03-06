<?php declare(strict_types=1);
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiifabric\Authorization\Tests;

use Yiifabric\Authorization\Rule;

/**
 * Checks if authorID matches userID passed via params.
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';
    public $reallyReally = false;

    /**
     * {@inheritdoc}
     */
    public function execute($user, $item, $params): bool
    {
        return $params['authorID'] == $user;
    }
}
