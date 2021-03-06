/*
Fiesta 2 Unnoficial Ranking
Copyright (C) 2014  HUEBR's Team

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

JPAK.Base64_Encoding="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";JPAK.ShowMessages=!1;ArrayBuffer.prototype.slice||(ArrayBuffer.prototype.slice=function(a,b){for(var c=ArrayBuffer(b-a),d=new Uint8Array(this),f=new Uint8Array(c),k=0,g=a;g<b;g++)f[k]=d[g],k++;return c});Array.prototype.clean=function(a){for(var b=0;b<this.length;b++)this[b]==a&&(this.splice(b,1),b--);return this};function JPAK(){}window.JPAK=JPAK;
JPAK.Uint8ArrayToString=function(a){for(var b="",c=0;c<a.byteLength;c++)b+=String.fromCharCode(a[c]);return b};var u8as=JPAK.Uint8ArrayToString;JPAK.String2ArrayBuffer=function(a){for(var b=new ArrayBuffer(a.length),c=new Uint8Array(b),d=0,f=a.length;d<f;d++)c[d]=a.charCodeAt(d)&255;return b};
JPAK.ArrayBufferToBase64=function(a){var b="";a=new Uint8Array(a);for(var c=a.byteLength,d=c%3,c=c-d,f,k,g,e,h=0;h<c;h+=3)e=a[h]<<16|a[h+1]<<8|a[h+2],f=(e&16515072)>>18,k=(e&258048)>>12,g=(e&4032)>>6,e&=63,b+=JPAK.Base64_Encoding[f]+JPAK.Base64_Encoding[k]+JPAK.Base64_Encoding[g]+JPAK.Base64_Encoding[e];1==d?(e=a[c],b+=JPAK.Base64_Encoding[(e&252)>>2]+JPAK.Base64_Encoding[(e&3)<<4]+"=="):2==d&&(e=a[c]<<8|a[c+1],b+=JPAK.Base64_Encoding[(e&64512)>>10]+JPAK.Base64_Encoding[(e&1008)>>4]+JPAK.Base64_Encoding[(e&
15)<<2]+"=");return b};JPAK.log=function(a){JPAK.ShowMessages&&console.log(a)};JPAK.jpakloader=function(a){void 0!==a&&(this.b=a.file,this.i=a.i||!1);this.a=[];this.d=!1};JPAK.jpakloader.prototype.CacheLoad=function(a){for(var b=0;b<this.a.length;b++)if(this.a[b].path==a)return this.a[b]};
JPAK.jpakloader.prototype.Load=function(){if(void 0!==this.b){var a=this,b=new XMLHttpRequest;b.open("GET",this.b,!0);b.responseType="arraybuffer";b.onprogress=function(b){if(b.lengthComputable&&void 0!=a.onprogress)a.onprogress({loaded:b.loaded,total:b.total,percent:(b.loaded/b.total*1E4>>0)/100})};b.onload=function(){if(200==this.status){var b=this.response,d=u8as(new Uint8Array(b.slice(0,5)));if("JPAK1"==d){if(JPAK.log("JPAK::jpakloader - Loaded file "+a.b+" successfully. JPAK1 Format"),d=(new DataView(b.slice(b.byteLength-
4,b.byteLength))).getUint32(0,!0),d=new Uint8Array(b.slice(d,b.byteLength-4)),d=JSON.parse(u8as(d)),a.h=d,a.e=b,a.d=!0,void 0!=a.onload)a.onload()}else if(JPAK.log("JPAK::jpakloader - Error loading file "+a.b+" (8000): Wrong File Magic. Expected JPAK1 got "+d),void 0!=a.onerror)a.onerror({text:"Wrong File Magic. Expected JPAK1 got "+d,errorcode:8E3})}};b.onreadystatechange=function(){if(4==this.readyState&&200!=this.status&&(JPAK.log("JPAK::jpakloader - Error loading file "+a.b+" ("+this.status+"): "+
this.statusText),void 0!=a.onerror))a.onerror({text:this.statusText,errorcode:this.status})};b.send()}else console.log("JPAK::jpakloader - No file to load!")};JPAK.jpakloader.prototype.FindDirectoryEntry=function(a){var b=this.h;if(this.d&&"/"!=a){a=a.split("/").clean("");for(var c="",d=!0,f=0;f<a.length;f++)if(c=a[f],c in b.directories)b=b.directories[c];else{d=!1;break}d||(b=void 0)}return b};
JPAK.jpakloader.prototype.FindFileEntry=function(a){var b=a.split("/").clean(""),b=b[b.length-1];a=a.replace(b,"");a=this.FindDirectoryEntry(a);if(void 0!=a&&b in a.files)return a.files[b]};
JPAK.jpakloader.prototype.ls=function(a){var b={files:[],dirs:[]};if(this.d)if(a=this.FindDirectoryEntry(a),void 0!=a){for(var c in a.files)b.files.push(a.files[c]);for(c in a.directories)b.k.push({name:a.directories[c].name,numfiles:a.directories[c].l})}else b.error="Directory not found!";else b.error="Not loaded";return b};
JPAK.jpakloader.prototype.GetFile=function(a,b){var c=this.FindFileEntry(a);b=b||"application/octet-binary";var d=this.CacheLoad(a);if(void 0!=c&&void 0==d)return d=this.e.slice(c.offset,c.offset+c.size),void 0!==c.c&&c.c&&(d=JPAK.f.g(d)),c=new Blob([(new Uint8Array(d)).buffer],{type:b}),this.a.push({path:a,type:b,blob:c,url:URL.createObjectURL(c),arraybuffer:d}),c;if(void 0!=d)return d.blob};
JPAK.jpakloader.prototype.GetFileURL=function(a,b){var c=this.CacheLoad(a);if(void 0==c){c=this.GetFile(a,b);if(void 0!=c)return URL.createObjectURL(c);JPAK.log('Error: Cannot find file "'+a+'"');return"about:blank"}return c.url};
JPAK.jpakloader.prototype.GetFileArrayBuffer=function(a,b){var c=this.FindFileEntry(a);b=b||"application/octet-binary";var d=this.CacheLoad(a);if(void 0!=c&&void 0==d)return d=this.e.slice(c.offset,c.offset+c.size),void 0!==c.c&&c.c&&(d=JPAK.f.g(d)),c=new Blob([(new Uint8Array(d)).buffer],{type:b}),this.a.push({path:a,type:b,blob:c,url:URL.createObjectURL(c),arraybuffer:d}),d;if(void 0!=d)return d.j;JPAK.log('Error: Cannot find file "'+a+'"')};
JPAK.jpakloader.prototype.GetBase64File=function(a,b){var c=this.GetFileArrayBuffer(a,b);return void 0==c?void 0:JPAK.ArrayBufferToBase64(c)};JPAK.jpakloader.prototype.GetHTMLDataURIFile=function(a,b,c){a=this.GetBase64File(a,b);return void 0===a?void 0:void 0!==c?"data:"+b+";charset="+c+";base64,"+a:"data:"+b+";base64,"+a};
