<?php
namespace Carpenstar\ByBitAPI\Core\Interfaces;

interface IEndpointInterface
{
    public function execute(int $mode): ICurlResponseDtoInterface;
}