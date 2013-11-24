/*
 * Password Strength (0.1.1)
 * by Sagie Maoz (n0nick.net)
 * n0nick@php.net
 *
 * This plugin will check the value of a password field and evaluate the
 * strength of the typed password. This is done by checking for
 * the diversity of character types: numbers, lowercase and uppercase
 * letters and special characters.
 *
 * Copyright (c) 2010 Sagie Maoz <n0nick@php.net>
 * Licensed under the GPL license, see http://www.gnu.org/licenses/gpl-3.0.html
 *
 *
 * NOTE: This script requires jQuery to work.  Download jQuery at www.jquery.com
 *
 */

(function($){

    var passwordStrength = new function()
    {
        this.countRegexp = function(val, rex)
        {
            var match = val.match(rex);
            return match ? match.length : 0;
        }

        this.getStrength = function(val, minLength)
        {
            var len = val.length;

            // too short =(
            if (len < minLength)
            {
                return 0;
            }

            var nums = this.countRegexp(val, /\d/g),
            lowers = this.countRegexp(val, /[a-z]/g),
            uppers = this.countRegexp(val, /[A-Z]/g),
            specials = len - nums - lowers - uppers;

            // just one type of characters =(
            if (nums == len || lowers == len || uppers == len || specials == len)
            {
                return 1;
            }

            var strength = 0;
            if (nums)	{
                strength+= 2;
            }
            if (lowers)	{
                strength+= uppers? 4 : 3;
            }
            if (uppers)	{
                strength+= lowers? 4 : 3;
            }
            if (specials) {
                strength+= 5;
            }
            if (len > 10) {
                strength+= 1;
            }

            return strength;
        }

        this.getStrengthLevel = function(val, minLength)
        {
            var strength = this.getStrength(val, minLength);
            switch (true)
            {
                case (strength <= 0):
                    return 1;
                    break;
                case (strength > 0 && strength <= 4):
                    return 2;
                    break;
                case (strength > 4 && strength <= 8):
                    return 3;
                    break;
                case (strength > 8 && strength <= 12):
                    return 4;
                    break;
                case (strength > 12):
                    return 5;
                    break;
            }

            return 1;
        }
    }

    $.fn.password_strength = function(options)
    {
        var settings = $.extend({
            'container' : null,
            'minLength' : 6,
            'texts' : {
                1 : 'Velmi snadné',
                2 : 'Snadné',
                3 : 'Normalní',
                4 : 'Silné heslo',
                5 : 'Velmi silné heslo'
            }
        }, options);

        return this.each(function()
        {
            if (settings.container)
            {
                var container = $(settings.container);
            }
            else
            {
                var container = $('<span/>').attr('class', 'password_strength');
                $(this).after(container);
            }

            $(this).keyup(function()
            {
                var val = $(this).val();
                if (val.length > 0)
                {
                    var level = passwordStrength.getStrengthLevel(val, settings.minLength);
                    var _class = 'password_strength_' + level;

                    if (!container.hasClass(_class) && level in settings.texts)
                    {
                        container.text(settings.texts[level]).attr('class', 'password_strength ' + _class);
                    }
                }
                else
                {
                    container.text('').attr('class', 'password_strength');
                }
            });
        });
    };

})(jQuery);

(function($){



    $.fn.password_control = function(options)
    {
        var settings = $.extend({
            'container' : null,
            'minLength' : 6,
            'passwordField': 'password',
            'texts' : {
                0 : 'kontrola hesla nesouhlasí',
                1 : 'kontrola hesla ok',
            }
        }, options);

        return this.each(function()
        {
            if (settings.container)
            {
                var container = $(settings.container);
            }
            else
            {
                var container = $('<span/>').attr('class', 'password_control');
                $(this).after(container);
            }

            $(this).keyup(function()
            {
                var val = $(this).val();
                if (val.length > 0)
                {
                    if ($('#'+settings.passwordField).val()!= val){
                        var level = 0;    
                    } else {
                        var level = 1;
                    }
                    
                    var _class = 'password_control_' + level;

                    if (!container.hasClass(_class) && level in settings.texts)
                    {
                        container.text(settings.texts[level]).attr('class', 'password_control ' + _class);
                    }
                }
                else
                {
                    container.text('').attr('class', 'password_control');
                }
            });
        });
    };

})(jQuery);


