<?php

class ApiTools
{
    public static function convertApiObjectsToIds($objects)
    {
        $ids = [];
        foreach ($objects as $object) {
            // var_dump($object);
            $ids[] = $object->id;
        }
        return $ids;
    }

}
