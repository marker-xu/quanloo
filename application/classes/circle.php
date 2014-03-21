<?php

/**
 * 圈子助手函数
 * @author wangjiajun
 */
class Circle
{
    /**
     * 圈内视频过滤Tag字符串化
     * @param array $tags
     * @return string
     */
    public static function filterTagToString($tags)
    {
        $tags = array_map(function ($value) {
            return $value['name'].':'.implode(',', $value['tag']);
        }, $tags);
        return implode(' | ', $tags);
    }
}