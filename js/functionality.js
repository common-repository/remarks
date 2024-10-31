function processNavButtonMouseover() {

   // 1. Deslect buttons, hide all divs
   jQuery('#display > div').hide();
   jQuery('.remarks_button_selected').attr('class', 'remarks_button');

   //jQuery('#nav_row_2 > div').addClass('nav_row_2_member');

   // 2. Figure out our stub
   var stub = this.id.split('_')[0]
   var bgColourClassName = stub + '_bg_colour';

   // 3. Arrange our button
   jQuery(this).addClass("remarks_button_selected");
   jQuery(this).addClass(bgColourClassName);

   // 4. Show our div
   var divToShow = stub + '_div';
   jQuery('#' + divToShow).show();
}

function processSubButtonMouseover() {

    var iFirstUnderscore = this.id.indexOf('_');
    var sectionName = this.id.substring(0, iFirstUnderscore);
    var stringRemainder = this.id.substring(iFirstUnderscore+1);
    var iSecondUnderscore = stringRemainder.indexOf('_')+1;
    var optionName = stringRemainder.substring(iSecondUnderscore);

    var idTable = sectionName + '_table';
    var idBar = sectionName + '_bar';
    var idPie = sectionName + '_pie';
    var idDiv = sectionName + '_div';

    if (optionName == 'table'){
      jQuery('#' + idTable).show();
      jQuery('#' + idBar).hide();
      jQuery('#' + idPie).hide();
    } else if (optionName == 'bar'){
      jQuery('#' + idTable).hide();
      jQuery('#' + idBar).show();
      jQuery('#' + idPie).hide();
    } else {
      jQuery('#' + idTable).hide();
      jQuery('#' + idBar).hide();
      jQuery('#' + idPie).show();
    }

    jQuery('#' + idDiv + ' .remarks_subbutton').attr('class', 'remarks_subbutton');
    jQuery(this).addClass(sectionName + '_bg_colour');
    jQuery(this).addClass('remarks_subbutton_selected');
}

jQuery(document).ready(function() {
  jQuery('.remarks_button').mouseover(processNavButtonMouseover);
  jQuery('.remarks_subbutton').mouseover(processSubButtonMouseover);

  // table initialisation
  //jQuery('tr:first-child').addClass('header');
  jQuery('tr').mouseover(function() {
    jQuery(this).addClass('selected');
  });

  jQuery('tr').mouseout(function() {
    jQuery(this).removeClass('selected');
  });

  //jQuery('#nav_row_2 > div').addClass('nav_row_2_member');
});