<?php

class VideoDataValidator implements InputDataValidator
{

    /**
     * Validate the incoming scraped data
     * @param $data
     * @return void
     */
    public static function validate($data): void
    {
        if (!is_array($data) || empty($data['success']) || !isset($data['options']) || empty($data['options']['src'])) {
            throw new InvalidArgumentException('data');
        }
    }
}
