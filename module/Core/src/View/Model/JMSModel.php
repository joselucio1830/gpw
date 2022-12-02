<?php
    /**
     * Created by PhpStorm.
     * User: José lucio
     * Date: 04/12/18
     * Time: 14:16
     */

    namespace Core\View\Model;
    use JMS\Serializer\Naming\CamelCaseNamingStrategy;
    use JMS\Serializer\SerializerBuilder;
    use Traversable;
    use Zend\Stdlib\ArrayUtils;
    use Zend\View\Model\JsonModel;

    class JMSModel extends JsonModel
    {
        public function serialize()
        {

            header('Content-type: application/json');
            header("Content-type: application/json; charset=utf-8");
            $variables = $this->getVariables();
            if ($variables instanceof Traversable) {
                $variables = ArrayUtils::iteratorToArray($variables);
            }

            $options = [
                'prettyPrint' => $this->getOption('prettyPrint'),
            ];

            $serializer = SerializerBuilder::create()
                ->setPropertyNamingStrategy(new \JMS\Serializer\Naming\IdenticalPropertyNamingStrategy())
                ->build();
            $sr = $serializer->serialize($variables, 'json');

            if (null !== $this->jsonpCallback) {
                return $this->jsonpCallback . '(' . $sr . ');';
            }
            return $sr;
        }
    }