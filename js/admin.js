jQuery(document).ready(function($){
  $('#acf-field-group-fields').on('blur', '.field-label,.acf-fc-meta-label input', function(){
    if($(this).val() != '-') {
      acf_name_selector = '#'+$(this).attr('id').replace('-label', '-name');
      acf_name_value = slugify($(this).val());
      if($(this).val().replace(new RegExp(" ",'g'),'_').toLowerCase() === $(acf_name_selector).val() || $(acf_name_selector).val() === ''){
        $(acf_name_selector).val(acf_name_value);
      }
    }
  });
});

function slugify ( str ) {
    
    var ru = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 
        'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i', 
        'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 
        'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 
        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 
        'щ': 'sch', 'ы': 'y', 'э': 'e', 'ю': 'yu', 'я': 'ya',
        'і' : 'i', 'ї' : 'yi', 'є' : 'ye', 'ґ' : 'g', 'й' : 'j',
        'ь' : '', 'ъ' : '', '-' : '_', ' ' : '_', '\'' :  '', '"' : '', '`' : ''
    }, n_str = [];
    
    for ( var i = 0; i < str.length; ++i ) {
       n_str.push(
              ru[ str[i] ]
           || ru[ str[i].toLowerCase() ] == undefined && str[i]
           || ru[ str[i].toLowerCase() ].replace(/^(.)/, function ( match ) { return match.toUpperCase() })
       );
    }
    
    return n_str.join('').toLowerCase();
}
