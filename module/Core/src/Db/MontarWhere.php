<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 28/10/16
 * Time: 08:06
 */

namespace Core\Db;


class MontarWhere
{
    protected $campos = [];

    protected $where;

    protected $condicoes;
    protected $projetoString = "";
    protected $condicoesReais = [];
    protected $values = [];

    public function __construct($campos)
    {
        $this->campos = $campos;
    }

    public function addCampo($key, $campo)
    {
        $this->campos[$key] = $campo;
    }


    /**
     * @return array
     */
    public function getCampos()
    {
        return $this->campos;
    }

    /**
     * @param array $campos
     * @return MontarWhere
     */
    public function setCampos($campos)
    {
        $this->campos = $campos;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        if (!empty($this->where)) {
            return $this->where;
        }
        return null;
    }

    public function getValues()
    {
        if (!empty($this->values))
            return $this->values;
        else
            return null;
    }

    /**
     * @param mixed $where
     * @return MontarWhere
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    public function separaString($sgring)
    {

        $regex = '/[\w\d]+ ?(==|>=|<=|=|<>|!=|is) ?("[^"]{0,}"|not null|null)/';

        preg_match_all($regex, $sgring, $this->condicoes);

        // replace condicoes
        $sgring = preg_replace($regex, '¨¨¨¨¨', $sgring);

        // replace ou
        $sgring = preg_replace("/ [ou|OU] /", "_¨¨_", $sgring);

        // replace e
        $sgring = preg_replace("/ [e|E] /", "_¨_", $sgring);

        // replace tudo que nao pertence a condição
        $sgring = preg_replace("/[^¨_()]/", "", $sgring);


        // refaz o processo montando a string de where como deve ser

        $sgring = preg_replace("/_¨¨_/", " OR ", $sgring);


        $sgring = preg_replace("/_¨_/", " AND ", $sgring);

        $this->projetoString = $sgring;

    }


    public function montar($string)
    {
        if (strlen(trim($string)) <= 0) {
            return false;
        }

        $this->separaString($string);
        $this->montaCondicoes();
        $this->montaWhere();


    }

    private function montaCondicoes()
    {
        foreach ($this->condicoes[0] as $condicao) {
            if (preg_match("/null$/", strtolower($condicao))) {
                $math = [];
                preg_match("/[\w\d]+ ?/", $condicao, $math);

                $this->addNullRealConcion(trim($math[0]), $condicao);

            } else {

                list($campo, $value) = preg_split("/==|>=|<=|=|<>|!=/", $condicao);

                $this->addRealConcion(trim($campo), $value, $condicao);
            }
        }

    }

    private function addNullRealConcion($campo, $condicao)
    {
        if (empty($this->campos[$campo])) {
            throw new \InvalidArgumentException("O campo {$campo} não pode ser pesquisado, pois não esta preconfigurado!");

        }
        $this->condicoesReais[] = preg_replace("/^$campo/", $this->campos[$campo], $condicao);

    }

    private function addRealConcion($campo, $value, $condicao)
    {
        if (empty($this->campos[$campo])) {
            throw new \InvalidArgumentException("O campo {$campo} não pode ser pesquisado, pois não esta preconfigurado!");
        }

        $condicao = $this->transformaCondicao($condicao, $campo, $value);

        $key = ":{$campo}" . rand(0, 99999);

        if (is_array($this->campos[$campo])) {
            $keyCampo = $this->campos[$campo]['campo'];
            $realValue = $this->montaValue(str_replace('"', "", $value), $this->campos[$campo]['tipo']);

        } else {
            $keyCampo = $this->campos[$campo];
            $realValue = $value;
        }

        $this->condicoesReais[] = str_replace($value, $key, str_replace($campo, $keyCampo, $condicao));
        $this->values[$key] = str_replace('"', "", $realValue);
    }

    private function montaWhere()
    {
        foreach ($this->condicoesReais as $codicao) {
            $this->projetoString = preg_replace("/¨¨¨¨¨/", $codicao, $this->projetoString, 1);
        }

        $this->where .= $this->projetoString;
    }

    private function transformaCondicao($condicao, $campo, &$value)
    {
        $cond = str_replace($value, '', str_replace($campo, '', $condicao));

        if ($cond == '=') {
            $value = "%{$value}%";
            return "{$campo} LIKE $value";
        } elseif ($cond == '==') {
            return "{$campo} = $value";
        } else {
            return $condicao;
        }

    }

    private function montaValue($value, $tipo)
    {

        switch ($tipo) {
            case 'date':
                $date = new \DateTime(str_replace('/', '-', $value));
                return $date->format('Y-m-d H:i:s');
                break;
            default:
                return $value;
                break;
        }
    }

    /**
     * @param $search ['sqlSearch' =>'nome like :search']
     * @return bool
     */
    public function search($search)
    {
        if (empty($search))
            return false;

        if (!empty($this->where)) {

            $this->where .= " AND ({$this->campos['sqlSearch']})";
        } else {
            $this->where = $this->campos['sqlSearch'];
        }

        $this->values['search'] = "%{$search}%";
    }


}
