<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('logcleaner');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <!-- Boutons de gestion du plugin -->
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="add">
                <i class="fas fa-plus-circle"></i>
                <br>

                <span>{{Ajouter}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>

                <span>{{Configuration}}</span>
            </div>
        </div>

        <legend><i class="fa fa-table"></i> {{Mes Equipements}}</legend>
        <!-- Champ de recherche -->
        <div class="input-group" style="margin-bottom:5px;">
            <input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
            <div class="input-group-btn">
            <a id="bt_resetObjectSearch" class="btn" style="width:30px"><i class="fas fa-times"></i>
            </a><a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
            </div>
        </div>
  
        <!-- Liste des équipements du plugin -->
        <div class="eqLogicThumbnailContainer">
            <?php
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
                echo '<br>';
                echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '</div>';
            }
            ?>
        </div>
    </div> <!-- /.eqLogicThumbnailDisplay -->

    <!-- Page de présentation de l'équipement -->
    <div class="col-xs-12 eqLogic" style="display: none;">
        <!-- barre de gestion de l'équipement -->
        <div class="input-group pull-right" style="display:inline-flex;">
            <span class="input-group-btn">
                <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
                <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
                </a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
                </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
                </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
                </a>
            </span>
        </div>
        <!-- Onglets -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i><span class="hidden-xs"> {{Équipement}}</span></a></li>
            <li role="presentation"><a href="#infotab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-info"></i><span class="hidden-xs"> {{Informations}}</span></a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i><span class="hidden-xs"> {{Commandes}}</span></a></li>
        </ul>
        <div class="tab-content">
            <!-- Onglet de configuration de l'équipement -->
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <!-- Partie gauche de l'onglet "Equipements" -->
                <!-- Paramètres généraux de l'équipement -->
                <br>
                <div class="row">
                    <div class="col-lg-7">
                        <form class="form-horizontal">
                            <fieldset>
                                <legend><i class="fas fa-wrench"></i> {{Général}}</legend>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                                    <div class="col-xs-11 col-sm-7">
                                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Objet parent}}</label>
                                    <div class="col-xs-11 col-sm-7">
                                        <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                            <option value="">{{Aucun}}</option>
                                            <?php
                                            $options = '';
                                            foreach ((jeeObject::buildTree(null, false)) as $object) {
                                                $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                            }
                                            echo $options;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Catégorie}}</label>
                                    <div class="col-sm-9">
                                        <?php
                                        foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                            echo '<label class="checkbox-inline">';
                                            echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                            echo '</label>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Options}}</label>
                                    <div class="col-xs-11 col-sm-7">
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
                <hr>
            </div><!-- /.tabpanel #eqlogictab-->



            <!-- Onglet Info -->
            <div role="tabpanel" class="tab-pane" id="infotab">
                <br>
                <div class="row">
                    <div class="col-lg-7">
                        <form class="form-horizontal">
                            <fieldset>
                                <legend><i class="fas fa-info" aria-hidden="true"></i> {{Général}}</legend>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{IP TV}}</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="text" class="eqLogicAttr form-control tooltips" 
                                            required pattern="^((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$"
                                            data-l1key="configuration" data-l2key="iptv" data-concat="1" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Durée}}<sub>(s)</sub></label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="eqLogicAttr form-control tooltips" 
                                            placeholder="30"
                                            data-l1key="configuration" data-l2key="duration" data-concat="1" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Position}}</label>
                                    <div class="col-sm-4">
                                        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="position"  data-concat="1" >
                                            <option value="0">{{Haut Droite}}</option>
                                            <option value="1">{{Haut Gauche}}</option>
                                            <option value="2">{{Bas Droite}}</option>
                                            <option value="3">{{Bas Gauche}}</option>
                                            <option value="4">{{Centre}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Taille du titre}}</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="eqLogicAttr form-control tooltips"
                                                placeholder="20" 
                                                data-l1key="configuration" data-l2key="titleSize" data-concat="1" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Taille du message}}</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="eqLogicAttr form-control tooltips"
                                                placeholder="14" 
                                                data-l1key="configuration" data-l2key="messageSize" data-concat="1" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Taille de l'image}}</label>
                                    <div class="col-sm-2">
                                        <div class="input-group">
                                            <input type="number" class="eqLogicAttr form-control tooltips"
                                                placeholder="240" 
                                                data-l1key="configuration" data-l2key="imageSize" data-concat="1" />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
                <hr>
            </div><!-- /.tabpanel #infotab-->

            <!-- Onglet des commandes de l'équipement -->
            <div role="tabpanel" class="tab-pane" id="commandtab">                
                <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br /><br />

                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th style="width:210px;">{{Nom}}</th>
                                <th style="width:210px;">{{Duréee}}</th>
                                <th style="width:120px;">{{Couleur titre}}</th>
                                <th style="width:120px;">{{Couleur message}}</th>
                                <th style="width:120px;">{{Coulor du fond}}</th>
                                <th>{{URL}}</th>
                                <th style="width:150px;">{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #commandtab-->
        </div><!-- /.tab-content -->
    </div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'logcleaner', 'js', 'logcleaner'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>