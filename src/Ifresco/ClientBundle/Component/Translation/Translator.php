<?php
namespace Ifresco\ClientBundle\Component\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator {

    /**
     * @param string $domain
     * @return mixed
     */
    public function getAllMessages($domain = 'messages') {
        $this->loadCatalogue($this->locale);

        return $this->catalogues[$this->locale]->all($domain);
    }
}

