<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 26/10/16
 * Time: 15:39
 */

namespace Core\Service;


use Core\Entity\AbstractEntity;
use Core\Entity\Validators\ValidateException;
use Core\Event\EventInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Tests\Fixtures\Entity;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Validator\Validation;

abstract class AbstractEnityService
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $sm;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var EntityManager
     */
    protected $em = null;

    /**
     * @var EventInterface
     */
    protected $evts = [];

    /**
     * AbstractEnityService constructor.
     * @param ServiceLocatorInterface $sm
     * @throws \Exception
     */
    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;

        $this->em = $sm->get(EntityManager::class);

        $this->addPreConfiguredEvents();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function addPreConfiguredEvents()
    {
        $config = $this->getSm()->get('config');

        $className = get_class($this);

        if (empty($config['coreEvents'][$className])) {
            return false;
        }

        if (!is_array($config['coreEvents'][$className])) {
            return false;
        }

        $events = $config['coreEvents'][$className];

        foreach ($events as $event) {
            /** @var EventInterface $evt */
            $evt = new $event($this->sm);

            if (!($evt instanceof EventInterface)) {
                throw  new \Exception("é necesseario ser uma instancia de " . EventInterface::class);
            }
            $this->evts[] = $evt;
        }

    }


    /**
     * @param $dados
     */
    protected function onCreate($dados)
    {
        /** @var EventInterface $evt */
        foreach ($this->evts as $evt) {
            $evt->onCreate($dados);
        }
    }

    protected function onDelete($dados)
    {
        /** @var EventInterface $evt */
        foreach ($this->evts as $evt) {
            $evt->onDelete($dados);
        }
    }

    protected function onExecute($dados, $evtName)
    {
        /** @var EventInterface $evt */
        foreach ($this->evts as $evt) {
            $evt->onExecute($dados, $evtName);
        }
    }

    public function onUpdate($dados)
    {
        /** @var EventInterface $evt */
        foreach ($this->evts as $evt) {
            $evt->onUpdate($dados);
        }
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getSm()
    {
        return $this->sm;
    }

    /**
     * @param ServiceLocatorInterface $sm
     * @return AbstractEnityService
     */
    public function setSm(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     * @return AbstractEnityService
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @param $entity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateEntity($entity)
    {
        if ($entity instanceof AbstractEntity) {
            $this->getEm()->merge($entity);
            $this->getEm()->flush($entity);
            $this->onUpdate(['entity' => $entity]);
        }
    }

    /**
     * @param AbstractEntity $entity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createEntity(AbstractEntity &$entity)
    {
        $this->getEm()->persist($entity);
        $this->getEm()->flush($entity);
        $this->onCreate(['entity' => $entity]);
    }

    /**
     * @param $entity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeEntity($entity)
    {
        if ($entity instanceof AbstractEntity) {
            $this->getEm()->remove($entity);
            $this->getEm()->flush($entity);
            $this->onDelete(['entity' => $entity]);
        }
    }

    /**
     * @return array|false
     */
    public function getErrors()
    {
        if (count($this->errors) > 0)
            return $this->errors;
        else
            return false;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    public function addError($key, $msg)
    {
        $this->errors[$key] [] = $msg;
    }

    /**
     * @return  ValidatorInterface
     */
    protected function getValidation()
    {
        $translator = new Translator('pt_BR');
        $translator->setFallbackLocales(['pt', 'pt_BR']);
        return Validation::createValidatorBuilder()
            ->setTranslator($translator)
            ->enableAnnotationMapping()
            ->getValidator();
    }

    public function mountErrors(ConstraintViolationListInterface $errors)
    {
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $this->addError($error->getPropertyPath(), $error->getMessage());
        }
    }

    /**
     * @param array $extraParams
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush($extraParams = [])
    {
        if (empty($extraParams['noFlush']) || !$extraParams['noFlush']) {
            $this->getEm()->flush();
        }
    }

    /**
     * @param $data
     * @param AbstractEntity $entity
     * @return AbstractEntity
     * @throws ValidateException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function create($data, AbstractEntity $entity)
    {

        // monta as entitys
        $entity->hydrate($data, $this->getEm());

        // valida os dados da entidade
        $this->validate($entity);

        $this->save($entity);

        return $entity;
    }

    /**
     * @param $data
     * @param $id
     * @param $entity
     * @return AbstractEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws ValidateException
     * @throws \Exception
     */
    public function updade($data, $id, $entityString)
    {
        /** @var AbstractEntity $entity */
        $entity = $this->getEm()->find($entityString, $id);

        if ($entity == null) {
            throw  new \Exception("A item não existe, para ser atualizado!");
        }

        // monta o hydrate
        $entity->hydrate($data, $this->getEm());

        // valida os dados
        $this->validate($entity);

        // salva os dados
        $this->save($entity);

        return $entity;
    }


    /**
     * @param $id
     * @param $entityString
     * @return AbstractEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function delete($id, $entityString)
    {
        /** @var AbstractEntity $entity */
        $entity = $this->getEm()->find($entityString, $id);

        if (!($entity instanceof AbstractEntity)) {
            throw  new \Exception("A item não existe!");
        }

        $this->removeEntity($entity);

        return $entity;

    }


    /**
     * @param AbstractEntity $entity
     * @return AbstractEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function saveUpdate(AbstractEntity $entity)
    {
        $this->getEm()->merge($entity);
        $this->getEm()->flush();
        $this->onUpdate([
            'entity' => $entity
        ]);
        return $entity;
    }

    /**
     * @param AbstractEntity $entity
     * @param bool $validate
     * @return AbstractEntity
     * @throws ValidateException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(AbstractEntity $entity, $validate = false)
    {
        if ($validate) {
            $this->validate($entity);
        }


        if (method_exists($entity, "getId") && $entity->getId() > 0) {
            return $this->saveUpdate($entity);
        } elseif (method_exists($entity, "getDtCad")) {
            $dt = $entity->getDtCad();
            if (!empty($dt)) {
                return $this->saveUpdate($entity);
            }
        }

        $this->getEm()->persist($entity);
        $this->getEm()->flush();
        $this->onCreate(['entity' => $entity]);
        return $entity;
    }

    /**
     * @param $entity
     * @throws ValidateException
     */
    private function validate($entity)
    {
        $validate = $this->getValidation();
        /** @var ConstraintViolationListInterface $violations */
        $violations = $validate->validate($entity);

        if (0 !== count($violations)) {
            // there are errors, now you can show them
            $erros = [];
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $erros[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            throw new ValidateException("Argumentos não validos!", -1, null, $erros);
        }
    }
}
