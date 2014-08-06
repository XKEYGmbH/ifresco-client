/*
   json.js
(modified 2006-09-07): added support for RHINO
(modified 2006-05-02): json.parse, json,stringify added
  USAGE:
  var jsObj = JSON.parse(jsonStr);
  var jsonStr = JSON.stringify(jsObj);
*/
if (!this.json) {
  var json = (function () {
      var m = {
              'b': 'b',
              't': 't',
              'n': 'n',
              'f': 'f',
              'r': 'r',
              '"' : '\"',
              '': ''
          },
          s = {
              array: function (x) {
                  var a = [], b, f, i, l = x.length, v;
                  for (i = 0; i < l; i += 1) {
                      v = x[i];
                      f = s[typeof v];
                      if (f) {
                          v = f(v);
                          if (typeof v == 'string') {
                              a[a.length] = v;
                              b = true;
                          }
                      }
                  }
                  return '['+a.join()+']';
              },
              'boolean': function (x) {
                  return String(x);
              },
              'null': function (x) {
                  return "null";
              },
              number: function (x) {
                  return isFinite(x) ? String(x) : 'null';
              },
              object: function (x) {
                  if (x) {

                      if (x instanceof Array) {
                          return s.array(x);
                      }
                      if (x.hashCode) return s.string(+x.toString());

                      var a = [], b, f, i, v;
                      for (i in x) {
                          v = x[i];
                          f = s[typeof v];
                          if (f) {

                              v = f(v);

                              if (typeof v == 'string') {
                                  a.push(""+s.string(i)+':'+ v+"");
                                  b = true;
                              }
                          }
                      }
                      return '{'+a.join()+'}';
                  }
                  return 'null';
              },
              string: function (x) {
                  if (/["x00-x1f]/.test(x)) {
                      x = x.replace(/([x00-x1f\"])/g, function(a, b) {
                          var c = m[b];
                          if (c) {
                              return c;
                          }
                          c = b.charCodeAt();
                          return 'u00' +
                              Math.floor(c / 16).toString(16) +
                              (c % 16).toString(16);
                      });
                  }
                  return '"' + x + '"';
              }
          };

     return {
        parse: function(s) {
           try {
              return !(/[^,:{}[]0-9.-+Eaeflnr-u nrt]/.test(s.replace(/"(.|[^"])*"/g,""))) && eval('(' + s + ')');
           } catch (e) {
              return false;
           }
        },
        stringify: s.object
     };
  })();
}