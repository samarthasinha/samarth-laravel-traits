This is Validation Trait for Laravel. This applies validations to eloquent models.
Just use this trait in your eloquent model and define getValidationRules() in that model like below example:

    protected function getValidationRules()
    {
        $array = [
            'category_id' => ['required', 'integer',
                Rule::exists('categories', 'id'),
            ],
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => ['required','email','max:191',
                Rule::unique('users')->ignore($this->id),
            ]
        ];
        if ($this->isValidationType('password'))
        {
            $array['password'] = 'required|string|min:6|max:191|confirmed';
        }
        return $array;
    }



The isValidationType() is helpful for conditional validations like I only need to validate password when I am doing a password change. You can set the condition for that in setValidationType().

Sometimes you dont want to do validations at all and just want the data to be added to your database. In that case use get_skip_validation() and set_skip_validation().

The errors() will show all the validation errors and you can add your custom errors by using addError().

And Finally validate your eloquent object by using validateObject(). Best way to do validation is to apply it in the saving event of the model.

Below is a example. Let User be the eloquent model which you want to validate.

    class User extends Model
    {
	//------------------ Define fillables and rest to your requirments -------------------------
        //------------------ Validations ---------------------------------
        protected function getValidationRules()
        {
            $array = [
                'category_id' => ['required', 'integer',
                    Rule::exists('categories', 'id'),
                ],
                'first_name' => 'required|string|max:191',
                'last_name' => 'required|string|max:191',
                'email' => ['required','email','max:191',
                    Rule::unique('users')->ignore($this->id),
                ],
                'username' => ['nullable','string','max:191',
                    Rule::unique('users')->ignore($this->id),
                ]
            ];
            if ($this->isValidationType('password'))
            {
                $array['password'] = 'required|string|min:6|max:191|confirmed';
            }
            return $array;
        }

	//------------------------ Event Callbacks --------------------------
        public function savingEvent()
        {
	    if (!$this->get_skip_validation())
            {
                if (!$this->validateObject())
                {
                    return false;
                }
            }
            return true;
        }
    }



Open your tinker and try there first.



Thank you.

