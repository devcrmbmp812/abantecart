<script>
$(document).ready(function(){
    $("input.colorpicker-element").each(function(){
        //var color = this.value;
        var color = $( this ).attr('value');
        //$( this ).css( "color", '#' + color );
        $( this ).css( "background-color", '#' + color );
    })

    $("input.colorpicker-element").change(function($mainElement) {
      var color = $( this ).attr('value');
      //$( this ).css( "color", '#' + color );
      $( this ).css( "background-color", '#' + color );
    })

    console.log('before');
    $("#editSettings_ultra_search_products").change(function(){
          console.log('after---');
    });





});
</script>
