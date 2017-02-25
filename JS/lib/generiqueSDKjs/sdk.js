/***********************************************************************************************
 * Javascript generic SDK
 *   Version: 0.1.1
 * Copyright 2016-2017 Thomas DUPONT
 * MIT License
 ************************************************************************************************/

"use strict";

var GeneriqueSDK = function ()
{
    /**
    * @var {string} apikey
    */
    this.apikey = "";

    /**
    * @var {string} apikey par use for query
    */
    this.apikeyPar = "apikey";

    /**
    * @var {object} private parameters
    *      {string} currentDomain, The current domain of request
    *      {bool}   connect, if hte cuser is connected
    *      {string} data, result of query
    *      {string} url, base url of external API
    *      {object} testDataSet, default dataSet (wikipedia API)
    */
    var params = {
        currentDomain: "",
        connect: false,
        data: "",
        url: "https://fr.wikipedia.org/w/api.php",
        testDataSet: {action : 'opensearch', search: 'Internet'}
    };

    /**
    * @var {object} all private methods
    */
    var privateM = {};

    /**
    * @function {public} void
    * @param    {string} unique apiKey (optionnal)
    */
    this.init = function (apiKey)
    {
        this.apikey = apiKey || "";
        privateM.sendJSONPRequest(
            {apiKey : this.apikey, domain: window.location.hostname},
            {
                callbackName: 'handleStuff',
                onSuccess: function(json){
                    if(json.success) {
                        param.connect = true;
                        return true;
                    } else {
                        console.log("connection failed");
                        return false;
                    }
                },
                onTimeout: function(){
                    console.log('timeout!');
                    return false;
                },
                timeout: 5
            },
            this.apikey,
            this.apikeyPar
        );
    };

    /**
    * @function {public} void
    * @param    {string} unique apiKey
    * @param    {string} apiKey par
    */
    this.setApikey = function (apikey, apikeyPar) {
        this.apikey = apikey;
        this.apikeyPar = apikeyPar || "apikey";
        params.connect = true;
    }

    /**
    * @function {public}    void
    * @param    {object}    dataSet, parameters of the query
    * @param    {function}  callback, onSuccess function
    * @param    {bool}      noConnection, set to true if you don't need to connect
    */
    this.get = function (dataSet, callback, noConnection)
    {

        var noConnection = noConnection || false;
        if(params.connect || noConnection) {

            var dataSet = dataSet || params.testDataSet, callback = callback || function() {};
            if(typeof dataSet !== 'object' || typeof callback !== 'function') {
                privateM.exception("Invalid parameters. "+typeof dataSet+" "+typeof callback+" given");
            }

            privateM.sendJSONPRequest(
                dataSet,
                {
                    callbackName: 'handleStuff',
                    onSuccess: function(json){
                        //console.log('success!', json);
                        callback(json);
                    },
                    onTimeout: function(){
                        console.log('timeout!');
                    },
                    timeout: 500
                },
                this.apikey,
                this.apikeyPar
            );

        } else {
            console.log("no connection available");
        }
    };

    /**
    * @function {public} bool
    * @return   {bool}   true on success, false else
    */
    this.disconnect = function ()
    {

        privateM.sendJSONPRequest(
            {action: "DISCONNECT"},
            {
                callbackName: 'handleStuff',
                onSuccess: function(json){
                    if(json.success) {

                        param.connect = false;
                        return true;

                    } else {

                        console.log("connection failed");
                        return false;

                    }
                },
                onTimeout: function(){
                    console.log('timeout!');
                    return false;
                },
                timeout: 5
            },
            this.apikey,
            this.apikeyPar
        );
    };

    /**
    * @function {public} void
    * @param    {string} url, set and check url of external API
    */
    this.setUrl = function(url)
    {

        if(
            /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url)
        ) {
            params.url = url;
        } else {
            privateM.exception(url+" is not a valide url");
        }

    }

    /**
    * @function {private} void
    * @param    {object}  parameters, query parameters
    * @param    {object}  options, options of request (onSuccess, onTimeOut, timeout)
    */
    privateM.sendJSONPRequest = function(parameters, options, apiKey, apikeyPar)
    {

        var callback_name = options.callbackName || 'callback',
           on_success = options.onSuccess || function(){},
           on_timeout = options.onTimeout || function(){},
           parameters = parameters || {},
           timeout = options.timeout || 10; // sec

        var timeout_trigger = window.setTimeout(
           function(){
               window[callback_name] = function(){};
               on_timeout();
           }, timeout * 1000);

        window[callback_name] = function(data){
           window.clearTimeout(timeout_trigger);
           params.data = data;
           on_success(data);
        }

        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = params.url+"?callback="+callback_name+"&"
            +apikeyPar+"="+apiKey+"&"+privateM.serialize(parameters);

        document.getElementsByTagName('head')[0].appendChild(script);
   };

   /**
   * @function {private} string
   * @param    {object}  obj, query parameters
   * @param    {string}  prefix, url prefix (optionnal)
   * @return   {string}  the encode string
   */
   privateM.serialize = function(obj, prefix)
   {

       var str = [], p;
       for(p in obj) {

           if (obj.hasOwnProperty(p)) {

               var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
               str.push((v !== null && typeof v === "object") ?
               privateM.serialize(v, k) :
               encodeURIComponent(k) + "=" + encodeURIComponent(v));

           }

       }
       return str.join("&");
   };

   /**
   * @function {private} void
   * @param {string} message, exception message
   */
   privateM.exception = function(message)
   {
       throw message;
   }

};
