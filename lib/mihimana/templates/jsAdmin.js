function showAdminMenu(domObject, wName) {
  var id = $(domObject).attr('id');
  var domObject = $(domObject);
  var menu = $('#admin_'+id);
  
  $('div.admin_menu').hide(200);
  menu.show(200);
  $.ajax({
    url: '<?php echo $baseUrl ?>loadDroits',
    data: {
      screen: '<?php echo $screen ?>',
      field: wName
    },
    success: function(data) {
      if (data.ok) {
        $('#cl_'+id).empty();
        $('#cr_'+id).empty();
        $('#el_'+id).empty();
        $('#er_'+id).empty();
        $('#vl_'+id).empty();
        $('#vr_'+id).empty();
        addToSelect('#cl_'+id, data.cdis);
        addToSelect('#cr_'+id, data.cass);
        addToSelect('#el_'+id, data.edis);
        addToSelect('#er_'+id, data.eass);
        addToSelect('#vl_'+id, data.vdis);
        addToSelect('#vr_'+id, data.vass);
        $('#a_'+id).val(data.a);
      }
      else {
        $('#err_'+id).html(data.error);
        $('#'+id+' table').hide();
      }
    }
  });
}

function submitDroits (domObject, wName) {
  var id = $(domObject).attr('id');
  function arrayMulti(multi) {
    var result = [];
    $(multi+' option').each(function(i, opt) {
      result[i] = $(opt).val();
    });
    return result;
  }
  var cr = arrayMulti('#cr_'+id);
  var er = arrayMulti('#er_'+id);
  var vr = arrayMulti('#vr_'+id);
  datas = {
    screen: '<?php echo $screen ?>',
    field: wName,
    c: JSON.stringify(cr),
    e: JSON.stringify(er),
    v: JSON.stringify(vr),
    a: $('#a_'+id).val()
  };
  
  $.ajax({
    url: '<?php echo $baseUrl ?>saveDroits',
    data: datas,
    success: function(data) {
      $('#admin_'+id).hide(200);
      if (data.ok) {
        //ajxPopup('Droits correctements enregistres');
      }
      else {
        ajxPopup('Erreur: '+data.error, 'Erreur');
      }
    }
  });
  
}
