<a class="perso <?php echo $perso->getClasse()->getCode(); ?>" href="<?php echo url_for2('persoFiche', array('id_perso' => $perso->getIdPerso(), 'nom' => $perso->getNom())) ?>" target="perso"><?php echo $perso->getNom(); ?></a>
