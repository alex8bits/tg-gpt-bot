/*
 * Объект-загрузчик файла на сервер.
 * Передаваемые параметры:
 * file       - объект File (обязателен)
 * url        - строка, указывает куда загружать (обязателен)
 * fieldName  - имя поля, содержащего файл (как если задать атрибут name тегу input)
 * onprogress - функция обратного вызова, вызывается при обновлении данных
 *              о процессе загрузки, принимает один параметр: состояние загрузки (в процентах)
 * oncopmlete - функция обратного вызова, вызывается при завершении загрузки, принимает два параметра:
 *              uploaded - содержит true, в случае успеха и false, если возникли какие-либо ошибки;
 *              data - в случае успеха в него передается ответ сервера
 *
 *              если в процессе загрузки возникли ошибки, то в свойство lastError объекта помещается
 *              объект ошибки, содержащий два поля: code и text
 */

var uploaderObject = function(params) {

    if(!params.file || !params.url) {
        return false;
    }

    this.xhr = new XMLHttpRequest();
    this.reader = new FileReader();

    this.progress = 0;
    this.uploaded = false;
    this.successful = false;
    this.lastError = false;

    var self = this;

    self.reader.onload = function() {
        self.xhr.upload.addEventListener("progress", function(e) {
            if (e.lengthComputable) {
                self.progress = (e.loaded * 100) / e.total;
                if(params.onprogress instanceof Function) {
                    params.onprogress.call(self, Math.round(self.progress));
                }
            }
        }, false);

        self.xhr.upload.addEventListener("load", function(){
            self.progress = 100;
            self.uploaded = true;
        }, false);

        self.xhr.upload.addEventListener("error", function(){
            self.lastError = {
                code: 1,
                text: 'Error uploading on server'
            };
        }, false);

        self.xhr.onreadystatechange = function () {
            var callbackDefined = params.oncomplete instanceof Function;
            if (this.readyState == 4) {
                if(this.status == 200) {
                    if(!self.uploaded) {
                        if(callbackDefined) {
                            params.oncomplete.call(self, false);
                        }
                    } else {
                        self.successful = true;
                        if(callbackDefined) {
                            params.oncomplete.call(self, true, this.responseText);
                        }
                    }
                } else {
                    self.lastError = {
                        code: this.status,
                        text: 'HTTP response code is not OK ('+this.status+')'
                    };
                    if(callbackDefined) {
                        params.oncomplete.call(self, false);
                    }
                }
            }
        };

        self.xhr.open("POST", params.url);

        var boundary = "xxxxxxxxx";
        self.xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary="+boundary);
        self.xhr.setRequestHeader("Cache-Control", "no-cache");

        var body = "--" + boundary + "\r\n";
        //v1.4.43 - транслитерация
		//body += "Content-Disposition: form-data; name='"+(params.fieldName || 'file')+"'; filename='" + params.file.name + "'\r\n";
		body += "Content-Disposition: form-data; name='"+(params.fieldName || 'file')+"'; filename='" + translite_js(params.file.name) + "'\r\n";
        body += "Content-Type: application/octet-stream\r\n\r\n";
        body += self.reader.result + "\r\n";
        body += "--" + boundary + "--";

		// Пилюля от слабоумия для Chrome, который гад портит файлы в процессе загрузки.
        if (!XMLHttpRequest.prototype.sendAsBinary) {
            XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
                function byteValue(x) {
                    return x.charCodeAt(0) & 0xff;
                }
                var ords = Array.prototype.map.call(datastr, byteValue);
                var ui8a = new Uint8Array(ords);
                this.send(ui8a.buffer);
            }
        }

        if(self.xhr.sendAsBinary) {
            // firefox
            self.xhr.overrideMimeType("application/octet-stream; charset=utf-8");
            self.xhr.sendAsBinary(body);
        } else {
            // chrome (W3C spec.)
            self.xhr.send(body);
        }

    };

    self.reader.readAsBinaryString(params.file);

    //v1.4.43 - транслитерация
	function translite_js(str){
		var arr={
			'а':'a', 'б':'b', 'в':'v', 'г':'g', 'д':'d', 'е':'e', 'ж':'zh', 'з':'z', 'и':'i', 'й':'i', 'к':'k', 'л':'l', 'м':'m', 'н':'n', 'о':'o', 'п':'p', 'р':'r', 'с':'s', 'т':'t', 'у':'u', 'ф':'f', 'ы':'y', 'э':'e', 'А':'a', 'Б':'b', 'В':'v', 'Г':'g', 'Д':'d', 'Е':'e', 'Ж':'zh', 'З':'z', 'И':'i', 'Й':'i', 'К':'k', 'Л':'l', 'М':'m', 'Н':'n', 'О':'o', 'П':'p', 'Р':'r', 'С':'s', 'Т':'t', 'У':'u', 'Ф':'f', 'Ы':'y', 'Э':'e', 'ё':'yo', 'х':'h', 'ц':'ts', 'ч':'ch', 'ш':'sh', 'щ':'shch', 'ъ':'', 'ь':'', 'ю':'yu', 'я':'ya', 'Ё':'yo', 'Х':'h', 'Ц':'ts', 'Ч':'ch', 'Ш':'sh', 'Щ':'shch', 'Ъ':'', 'Ь':'','Ю':'yu', 'Я':'ya'};
		var replacer=function(a){
			return arr[a]||a
		};
		return str.replace(/[А-яёЁ]/g,replacer)
	}
};
