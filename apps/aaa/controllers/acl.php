<?php

defined ('__MEICAN') or die ("Invalid access.");

include_once 'libs/controller.php';
include_once 'libs/auth.php';

include_once 'apps/aaa/models/group_info.inc';
include_once 'apps/aaa/models/user_info.inc';
include_once 'apps/aaa/models/aros_acos.inc';
include_once 'apps/aaa/models/aros.inc';
include_once 'apps/aaa/models/acos.inc';

include_once 'apps/bpm/models/request_info.inc';

include_once 'apps/circuits/models/flow_info.inc';
include_once 'apps/circuits/models/reservation_info.inc';
include_once 'apps/circuits/models/timer_info.inc';

include_once 'apps/topology/models/device_info.inc';
include_once 'apps/topology/models/domain_info.inc';
include_once 'apps/topology/models/network_info.inc';
include_once 'apps/topology/models/urn_info.inc';


class acl extends Controller {

    public function acl() {
        $this->app = 'aaa';
        $this->controller = 'acl';
        $this->defaultAction = 'show';
    }

    public function show() {
        $aros_acos = new aros_acos();
        $allRights = $aros_acos->fetch(FALSE);

        if ($allRights) {
            $rights = array();
            
            foreach ($allRights as $r) {
                $right = new stdClass();
            
                $right->id = $r->perm_id;
                
                $right->create = ($r->create) ? $r->create : "-";
                $right->read = ($r->read) ? $r->read: "-";
                $right->update = ($r->update) ? $r->update : "-";
                $right->delete = ($r->delete) ? $r->delete : "-";

                $grp_manager = new Aros();
                $grp_manager->aro_id = $r->aro_id;
                $result_mger = $grp_manager->fetch(FALSE);
                
                $aro = $result_mger[0];
                
                $aro_obj_descr = "-";
                if (($aro->obj_id) && class_exists($aro->model)) {
                    $model = new $aro->model;

                    if (method_exists($model, "getPrimaryKey")) {
                        $pk = $model->getPrimaryKey();

                        $model->$pk = $aro->obj_id;
                        $return = $model->fetch(FALSE);
                        $attr = $return[0]->getValidInds();

                        $temp = array();
                        foreach ($attr as $at_name) {
                            if (($return[0]->attributes[$at_name]->usedInInsert) && !($return[0]->attributes[$at_name]->forceUpdate))
                                $temp[] = "$at_name: " . $return[0]->$at_name;
                        }
                        $aro_obj_descr = implode("<br>", $temp);
                    }
                }
                
                $right->aro_obj_id = $aro->obj_id;
                $right->aro_obj = $aro_obj_descr;
                $right->aro_model = $aro->model;
                
                //Framework::debug("temp", implode("<br>", $temp));
                
//                switch ($aro->model) {
//                    case "group_info":
//                        $right->aro = $return[0]->grp_descr;
//                        break;
//                    case "user_info":
//                        $right->aro = $return[0]->usr_login;
//                        break;
//                    default:
//                        $right->aro = "$aro->model -> $aro->obj_id";
//                }

                $grp_managed = new Acos();
                $grp_managed->aco_id = $r->aco_id;
                $result_mged = $grp_managed->fetch(FALSE);
                
                $aco = $result_mged[0];
                
                $aco_obj_descr = ($aco->obj_id) ? "-" : "void";
                if (($aco->obj_id) && class_exists($aco->model)) {
                    $model = new $aco->model;

                    if (method_exists($model, "getPrimaryKey")) {
                        $pk = $model->getPrimaryKey();

                        $model->$pk = $aco->obj_id;
                        $return = $model->fetch(FALSE);
                        $attr = $return[0]->getValidInds();

                        $temp = array();
                        foreach ($attr as $at_name) {
                            if (($return[0]->attributes[$at_name]->usedInInsert) && !($return[0]->attributes[$at_name]->forceUpdate))
                                $temp[] = "$at_name: " . $return[0]->$at_name;
                        }
                        $aco_obj_descr = implode("<br>", $temp);
                    }
                }
                
                $right->aco_model = $aco->model;
                $right->aco_obj = $aco_obj_descr;
                $right->aco_obj_id = $aco->obj_id;

                $right->editable = ($result_mger && $result_mged) ? TRUE : FALSE;
                //$right->editable=TRUE;
                $right->model = ($r->model) ? $r->model : _("All");
                
                $rights[] = $right;
            }
            $this->setAction('show');
            
            $this->setArgsToBody($rights);
            
            $aros = get_tree_models(new Aros());
            Framework::debug("aros",$aros);
            $acos = get_tree_models(new Acos());
            Framework::debug("acos",$acos);
            
            $this->setArgsToScript(array(
                "allow_desc_string" => _("Allow"),
                "deny_desc_string" => _("Deny"),
                "str_delete_acl" => _("Delete ACL?"),
                "str_acl_deleted" => _("ACL deleted"),
                "str_acl_not_deleted" => _("Fail to delete ACL"),
                "str_all" => _("All"),
                "confirmMessage" => _("Save modifications?"),
                "fillMessage" => _("Please fill in all the fields"),
                "aros" => $aros,
                "acos" => $acos,
            ));
            
            $this->addScript('acl');
            $this->setInlineScript('acl_init');
        } else {
            $this->setAction('empty');

            $args = new stdClass();
            $args->title = _("Access Control List");
            $args->message = _("You can't see any access control, click the button below to add one");
            $this->setArgsToBody($args);
        }

        $this->render();
    }
    
    public function get_aros_acos() {
        
        //$aros_models = array("group_info", "user_info");
        //$acos_models = array("device_info", "domain_info", "flow_info", "network_info", "reservation_info", "timer_info", "request_info", "urn_info", "user_info");
                
        $args = new stdClass();
        
        $args->aros = get_tree_models(new Aros());
        $args->acos = get_tree_models(new Acos());
        
        $this->setLayout('empty');
        $this->setAction('ajax');
        $this->setArgsToBody($args);
        $this->render();
    }

    public function singleDelete() {
        if ($perm_id = Common::POST('perm_id')) {
            $acc = new aros_acos();
            $acc->perm_id = $perm_id;
            $result = $acc->delete();
            $this->setArgsToBody($result);
        }
        
        $this->setLayout('empty');
        $this->setAction('ajax');

        $this->render();
    }
    
    public function delete() {
        $del_accs = Common::POST("del_checkbox");

        if ($del_accs) {
            $count = 0;
            foreach ($del_accs as $perm_id) {
                $acc = new aros_acos();
                $acc->perm_id = $perm_id;
                $tmp = $acc->fetch(FALSE);
                $result = $tmp[0];
                if ($acc->delete())
                    $count++;
            }
            switch ($count) {
                case 0:
                    $this->setFlash(_("No access control was deleted"), "warning");
                    break;
                case 1:
                    $this->setFlash(_("One access control was deleted"), "success");
                    break;
                default:
                    $this->setFlash("$count " . _("access controls were cancelled"), "success");
                    break;
            }
        }

        $this->show();
    }

    public function update() {
        $updated = NULL;
        $added = NULL;

        $updated = $this->modify(Common::POST("acl_editArray"));
        $added = $this->add(Common::POST("acl_newArray"));
        
        if ($updated || $added)
            $this->setFlash(_("ACL updated"), 'success');

        $this->show();
    }

    private function add($accessData) {
        $cont = 0;

        if ($accessData) {
            foreach ($accessData as $acl) {

                $aro = new Aros();
                $aro->model = $acl[0];
                $aro->obj_id = $acl[1];
                $aros = $aro->fetch(FALSE);

                foreach ($aros as $a) {
                    $aco = new Acos();
                    $aco->model = $acl[2];
                    $aco->obj_id = $acl[3];
                    $acos = $aco->fetch(FALSE);

                    $aros_acos = new aros_acos();
                    $aros_acos->aro_id = $a->aro_id;

                    $aros_acos->aco_id = $acos[0]->aco_id;

                    $aros_acos->model = ($acl[4] == "all") ? NULL : $acl[4];

                    $aros_acos->create = ($acl[5] == -1) ? NULL : $acl[5];
                    $aros_acos->read = ($acl[6] == -1) ? NULL : $acl[6];
                    $aros_acos->update = ($acl[7] == -1) ? NULL : $acl[7];
                    $aros_acos->delete = ($acl[8] == -1) ? NULL : $acl[8];

                    if ($aros_acos->insert())
                        $cont++;
                }
            }
        }
        
        return $cont;
    }

    private function modify($accessData) {
        $cont = 0;

        if ($accessData) {
            foreach ($accessData as $acl) {

                $aro = new Aros();
                $aro->model = $acl[1];
                $aro->obj_id = $acl[2];
                $aros = $aro->fetch(FALSE);

                foreach ($aros as $a) {
                    $aco = new Acos();
                    $aco->model = $acl[3];
                    $aco->obj_id = $acl[4];
                    $acos = $aco->fetch(FALSE);

                    $aros_acos = new aros_acos();
                    $aros_acos->perm_id = $acl[0];
                    $aros_acos->aro_id = $a->aro_id;

                    $aros_acos->aco_id = $acos[0]->aco_id;

                    $aros_acos->model = ($acl[5] == "all") ? NULL : $acl[5];

                    $aros_acos->create = ($acl[6] == -1) ? "NULL" : $acl[6];
                    $aros_acos->read = ($acl[7] == -1) ? "NULL" : $acl[7];
                    $aros_acos->update = ($acl[8] == -1) ? "NULL" : $acl[8];
                    $aros_acos->delete = ($acl[9] == -1) ? "NULL" : $acl[9];

                    if ($aros_acos->update())
                        $cont++;
                }
            }
        }
        
        return $cont;
    }

}

function get_tree_models($tree_object) {
    if (!is_a($tree_object, "Tree_Model")) {
        return FALSE;
    }

    $models = array();

    if ($nodes = $tree_object->fetch(FALSE)) {
        foreach ($nodes as $node) {

            if (class_exists($node->model)) {
            $obj_model = new $node->model;
            if (is_a($obj_model, "Model")) {
            $pk = $obj_model->getPrimaryKey();
            $obj_model->$pk = $node->obj_id;
            
            if ($res = $obj_model->fetch()) {

            $item = $res[0];

            $model = new stdClass();
            $model->id = $node->model;
            $model->name = $node->model;

            $attr = $item->getValidInds();
            
            $temp = array();
            if (!empty($obj_model->displayField)){
                $temp[] = $item->{$obj_model->displayField};
            } else
                foreach ($attr as $at_name) {
                    if (($item->attributes[$at_name]->usedInInsert) && !($item->attributes[$at_name]->forceUpdate))
                        $temp[] = "$at_name: " . $item->$at_name;
                }

            $obj = new stdClass();
            $obj->id = $node->obj_id;
            $obj->name = implode("; ", $temp);

            $model->objs = array();
            $model->objs[] = $obj;

            $modelInArray = FALSE;

            foreach ($models as $model_index => $m) {
                if ($m->id == $model->id) {
                    $modelInArray = TRUE;
                    $objInModel = FALSE;
                    foreach ($m->objs as $o) {
                        if ($o->id == $obj->id) {
                            $objInModel = TRUE;
                        }
                        
                        break;
                    }
                    
                    if (!$objInModel)
                        array_push($models[$model_index]->objs, $obj);
                
                    break;
                }
            }

            if (!$modelInArray) {
                array_push($models, $model);
            }
            }
            }
            }
        }
    }
    
    return $models;
}

function temp_get_model_objs($models_array=array()) {
    $models = array();

    foreach ($models_array as $m) {
        $model = new stdClass();

        $model->id = $m;
        $model->name = $m;

        $tmp_mod_class = new $m;
        $ret = $tmp_mod_class->fetch(FALSE);

        $objs = array();
        foreach ($ret as $item) {
            $obj = new stdClass();

            $pk = $item->getPrimaryKey();
            $obj->id = $item->$pk;

            $attr = $item->getValidInds();

            $temp = array();
            foreach ($attr as $at_name) {
                if (($item->attributes[$at_name]->usedInInsert) && !($item->attributes[$at_name]->forceUpdate))
                    $temp[] = "$at_name: " . $item->$at_name;
            }
            $obj->name = implode("; ", $temp);

            $objs[] = $obj;
        }
        
        $model->objs = $objs;

        $models[] = $model;
    }

    return $models;
}

?>