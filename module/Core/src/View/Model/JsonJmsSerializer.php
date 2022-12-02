<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 26/10/16
 * Time: 18:39
 */

namespace Core\View\Model;


use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\JsonModel;

class JsonJmsSerializer extends JsonModel
{
    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $variables = $this->getVariables();
        if ($variables instanceof Traversable) {
            $variables = ArrayUtils::iteratorToArray($variables);
        }




        $jsm = SerializerBuilder::create()
            ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
            ->build();
        if (null !== $this->jsonpCallback) {
            return $this->jsonpCallback . '(' . $jsm->serialize($variables, 'json') . ');';
        }
        return $jsm->serialize($variables, 'json');
    }
}