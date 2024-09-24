<?php

namespace App\Entity;

enum EtatEnum: string
{
    case EN_CREATION = 'En création';
    case OUVERTE = 'Ouverte';
    case CLOTUREE = 'Cloturée';
    case EN_COURS = 'En cours';
    case TERMINEE = 'Terminée';
    case ANNULEE = 'Annulée';
    case HISTORISEE  = 'Historisée';
}
