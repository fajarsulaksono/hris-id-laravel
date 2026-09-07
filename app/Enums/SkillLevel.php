<?php

namespace App\Enums;

enum SkillLevel: string
{
    case BEGINNER = 'b';
    case INTERMEDIATE = 'i';
    case ADVANCED = 'a';
    case EXPERT = 'e';
}