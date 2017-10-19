<div class="account_creation" style="padding-bottom: 20px;">

    <h3 class="page-subheading">{l s='CPF' mod='paggi'}</h3>      
      
              

        <div id="field_cpf">
            
            <div id="validate-cpf" class="required form-group">
                    <label for="cpf">{l s='CPF:' mod='paggi'} <sup>*</sup></label>
                    <input type="text" class="form-control" id="cpf" name="cpf" value="{$cpf}" />
                    <p class="required" id="erro_cpf" style="display:none;"></p>
            </div>            
           
        </div>

      
</div>

 <input type="hidden" name="validatedoc" id="validatedoc" value="{$urlValidateDoc}" />


<script type="text/javascript">
{literal}
    
$(function(){   
   
    
    var options = {
        clearIfNotMatch: true,
        onComplete: function(cpf) {
            validateDoc(cpf);
        }
    };
        
    // Ação para o campo Cpf
    $('#cpf').mask('999.999.999-99', options);
 
    
    // form-error ou form-ok
});

function validateDoc(cpf) {
    $('#erro_cpf').hide();  
    
    $.ajax({
        type: "GET",
        url: $('#validatedoc').val(),
        data: {cpf: cpf},
        dataType: "json",
        success: function (json){
            if ( json.status === true ){
                $('#validate-cpf').attr('class','required form-group form-ok');
                $('#submitAccount:disabled').removeAttr('disabled');
            }else{
                $('#erro_cpf').empty();
                $('#erro_cpf').append(json.error);
                $('#erro_cpf').show('slow');
                
                $('#validate-cpf').attr('class','required form-group form-error');
                $('#submitAccount').attr('disabled','disabled');
            }
        }
    });
}

function clearFields() {
    $('#erro_cpf').hide();
   
    
    $('#validate-cpf').removeClass('form-ok');
    $('#validate-cpf').removeClass('form-error');
  
    
    $('#cpf').val('');

}

{/literal}
</script>