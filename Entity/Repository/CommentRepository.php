<?php

namespace Awaresoft\CommentBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CommentRepository class.
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class CommentRepository extends EntityRepository
{

}