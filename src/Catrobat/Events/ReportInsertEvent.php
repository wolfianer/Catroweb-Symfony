<?php

namespace App\Catrobat\Events;

use App\Entity\ProgramInappropriateReport;
use Symfony\Contracts\EventDispatcher\Event;

class ReportInsertEvent extends Event
{
  protected ?string $category;


  protected ProgramInappropriateReport $program;

  public function __construct(?string $category, ProgramInappropriateReport $program)
  {
    $this->category = $category;
    $this->program = $program;
  }

  public function getCategory(): ?string
  {
    return $this->category;
  }



  public function getReport(): ProgramInappropriateReport
  {
    return $this->program;
  }
}
