<?php

namespace Core\Entity;

use Core\Entity\Validators\ValidateException;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AbstractEntity
 * @package Core\Entity
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractEntity
{
    /**
     * @var int
     * @Serializer\Exclude()
     */
    public static $CODE_EXCEPTION = -1;

    public function hydrate(array $data, EntityManager $em)
    {
        $hydrate = new DoctrineObject($em);
        return $hydrate->hydrate($data, $this);
    }

    public function extract(EntityManager $em)
    {
        $hydrate = new DoctrineObject($em);
        return $hydrate->extract($this);
    }

    public static abstract function getCampos();

    /**
     * @return  ValidatorInterface
     */
    private function getValidation()
    {
        $translator = new Translator('pt_BR');
        $translator->setFallbackLocales(['pt', 'pt_BR']);
        return Validation::createValidatorBuilder()
            ->setTranslator($translator)
            ->enableAnnotationMapping()
            ->getValidator();
    }

    /**
     * @param $entity
     * @throws ValidateException
     */
    protected function validate()
    {
        $validate = $this->getValidation();
        /** @var ConstraintViolationListInterface $violations */
        $violations = $validate->validate($this);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            $erros = [];
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $erros[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            throw new ValidateException("Argumentos não validos!", self::$CODE_EXCEPTION, null, $erros);
        }
    }
}