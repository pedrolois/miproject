       function add_optionsORgroups()
        {
            var number_options = document.getElementById('amount_options').value;

           if(number_options >1) {
               document.getElementById('label_column_name').innerHTML = "<label id='label_column_name'>Group Name: <input type='text' name='name_column' required></label><br>";

               for (i = 1; i <= number_options; i++)
               {
                   document.getElementById('div-options_groups').innerHTML += 
                  "<label>option"+i+": <input type='text' value='' name='options["+i+"]'></label><br>";
               }
           }
        }
        function clear_options()
        {
            document.getElementById('label_column_name').innerHTML = "<label id='label_column_name'>Name Column: <input type='text' name='name_column' required></label><br>";
            document.getElementById('div-options_groups').innerHTML ="";
            document.getElementById('amount_options').value ="0";

        }


