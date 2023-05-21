
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

 
/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
});

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  //console.log("_cmd:", _cmd);
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {};
  }
  if(!_cmd.logicalId){
    _cmd.logicalId = 'mode';
    _cmd.type = 'action';
    _cmd.subType = 'message';
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<div class="input-group">';
  tr += '<span class="cmdAttr" data-l1key="id" style="display : none;"></span>';
  tr += '<span class="cmdAttr" data-l1key="logicalId" style="display:none;"></span>';
  tr += '<span class="type" type="' + init(_cmd.type) + '" style="display:none;">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style="display:none;"></span>';

  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de la commande}}">';
  tr += '</div>';
  tr += '</td>';

  tr += '<td>';
  tr += '<div class="input-group">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="duration">';
  tr += '</div>';
  tr += '</td>';

  tr += '<td>';
  tr += '<div class="input-group">';
  tr += '<input class="cmdAttr form-control input-sm" placeholder="#000000" data-l1key="configuration" data-l2key="titleColor">';
  tr += '</div>';
  tr += '</td>';

  tr += '<td>';
  tr += '<div class="input-group">';
  tr += '<input class="cmdAttr form-control input-sm" placeholder="#000000" data-l1key="configuration" data-l2key="messageColor">';
  tr += '</div>';
  tr += '</td>';

  tr += '<td>';
  tr += '<div class="input-group">';
  tr += '<input class="cmdAttr form-control input-sm" placeholder="#ffffff" data-l1key="configuration" data-l2key="backgroundColor">';
  tr += '</div>';
  tr += '</td>';

  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="url">';
  tr += '</td>';

  tr += '<td>';
  if (is_numeric(_cmd.id)) {
     tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
     tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>';
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>'
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  var tr = $('#table_cmd tbody tr').last();
  jeedom.eqLogic.buildSelectCmd({
    id:  $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {type: 'info'},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result);
      tr.setValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    }
  });
}
  

// $(".eqLogic").delegate(".listCmdInfo", 'click', function () {
//   var el = $(this).closest('.form-group').find('.eqLogicAttr');
//   jeedom.cmd.getSelectModal({
//     cmd: {
//       type: 'info'
//     }
//   }, function (result) {
//     el.value(result.human);
//   });
// });

// $(".eqLogic").delegate(".listCmdAction", 'click', function () {
//     //console.log("--------- listCmdAction");
//     var type = $(this).attr('data-type');
//     var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');

//     jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
//         el.value(result.human);
//         jeedom.cmd.displayActionOption(el.value(), '', function (html) {
//           el.closest('.' + type).find('.actionOptions').html(html);
//           taAutosize();
//         });
//     });
//   });

// $(".eqLogic").delegate(".listAction", 'click', function () {
// 	//console.log("--------- listAction");
//   var type = $(this).attr('data-type');
//   var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');

//   jeedom.getSelectActionModal({}, function (result) {
//     el.value(result.human);
//     jeedom.cmd.displayActionOption(el.value(), '', function (html) {
//       el.closest('.' + type).find('.actionOptions').html(html);
//       taAutosize();
//     });
//   });
// });


/*** Save ***/
function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }
  _eqLogic.configuration.Pipup = $('#div_confPipups .confPipup').getValues('.expressionAttr');
  _eqLogic.configuration.action = $('#div_confActions .confAction').getValues('.expressionAttr');

  // console.log('saveEqLogic:', _eqLogic);
  return _eqLogic;
}

function printEqLogic(_eqLogic) {
  // console.log('printEqLogic:', _eqLogic);

  $('#div_confPipups').empty();
  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.Pipup)) {
      for (var i in _eqLogic.configuration.Pipup) {
        //console.log("printEqLogic.addConfPipups", _eqLogic.configuration.Pipup[i]);
        addConfPipups(_eqLogic.configuration.Pipup[i]);
      }
    }
  }

  $('#div_confActions').empty();
  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.action)) {
      for (var i in _eqLogic.configuration.action) {
        //console.log("printEqLogic.addConfActions", _eqLogic.configuration.action[i]);
        addConfActions(_eqLogic.configuration.action[i]);
      }
    }
  }
}


// Sondes triables
$("#div_confPipups").sortable({
  axis: "y",
  cursor: "move",
  items: ".confPipup",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
});
