<?php

/**
 * Skeleton subclass for representing a row from the 'wow_wishlist_objet' table.
 *
 *
 *
 * This class was autogenerated by Propel 1.6.4-dev on:
 *
 * 12/23/11 18:12:12
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.lib.model
 */
class WishlistObjet extends BaseWishlistObjet
{
    /**
     * surcharge du setObjet pour permettre de retrouver une attribution
     * @param Objet $objet à ajouter
     */
    public function setObjet(Objet $objet = null)
    {
        parent::setObjet($objet);

        if (is_null($objet)) {
            $this->setAttribution(null);

            return;
        }

        $this->setAttribution(
            AttributionQuery::create()
                // ->filterByObjet($objet->getGenerated()) // @TODO : tester si nécessaire
                ->filterByObjet($objet)
                ->filterByPerso($this->getWishlist()->getPerso())
                ->findOne()
        );
    }

    /**
     * défini que cette objet de wishlist a été ramassé
     */
    public function setLooted($tmp = false, $gain = null, $idSoiree = null)
    {
        $perso = $this->getWishlist()->getPerso();

        if (!$this->getIdAttribution()) {
            // création de l'attrib si pas encore
            $attribution = new Attribution();
            $attribution->setPerso($perso);
            if($idSoiree) $attribution->setIdSoiree($idSoiree);

            // l'objet attribué est celui de la wishlist
            $attribution->setIdObjet($this->getIdObjet());

            $attribution->setPrix( // l'ordre sert à la fois de prio et de cout d'objet
                isset($gain) ? $gain : $this->getWishlist()
                    ->getTypeWishlist()->getOrdre()
            );
        } else {
            $attribution = $this->getAttribution();
        }

        $attribution->setTmp($tmp);
        $attribution->save();

        // si l'objet est un token, il sert qu'à un seul équipement en théorie
        if ($this->getObjet()->isToken()) {
            $this->setIdAttribution($attribution->getIdAttribution());
            $this->save();
        }

        // sinon on met à jour toutes les wishlists de ce perso
        else
            WishlistObjetQuery::create()
                ->filterByIdSlot($this->getIdSlot()) // concernant le même slot d'équipement
                ->filterByIdObjet($this->getIdObjet()) // le même objet
                ->filterByIdWishlist(
                    WishlistQuery::create()->select('IdWishlist')
                        ->filterByIdPerso($this->getWishlist()->getIdPerso()) // pour le même perso
                        ->find()->getData()
                )
                ->update(array(
                    'IdAttribution' => $attribution->getIdAttribution()
                ));

        // mise à jour du compte
        $perso->getCompte()->addLoot($attribution->getPrix());

        return $this;
    }

    /**
     * retire un loot au perso
     * @param Item|string $item     objet ou identifiant d'objet à ajouter
     * @param int         $idSoiree id de la soirée d'obtention
     */
    public function unsetLooted($tmp = false)
    {
        $attribution = $this->getAttribution();

        if(!$attribution || $attribution->getTmp() != $tmp)

            return $this;

        // mise à jour du compte
        $this->getWishlist()
            ->getPerso()->getCompte()
            ->addLoot(-($attribution->getPrix()));

        $attribution->delete();

        return $this;
    }

} // WishlistObjet