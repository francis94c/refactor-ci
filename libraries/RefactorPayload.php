<?php
declare(strict_types=1);

defined('BASEPATH') OR exit('No direct script access allowed');

class RefactorPayload
{
  protected $payload;

  public function __construct($payload=[])
  {
    $this->payload = $payload;
  }
  public function toArray():array
  {

  }
}
?>
