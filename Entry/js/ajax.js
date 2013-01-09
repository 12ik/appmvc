var xmlHttp=new createXmlHttpRequestObject();
function  createXmlHttpRequestObject()
{
	var xmlHttp;
	try
	{
		xmlHttp=new XMLHttpRequest();
	}
	catch(e)
	{
		var xmlHttpVersions=new Array("MSXML2.XMLHTTP.6.0",
										"MSXML2.XMLHTTP.5.0",
										"MSXML2.XMLHTTP.4.0",
										"MSXML2.XMLHTTP.3.0",
										"MSXML2.XMLHTTP",
										"Microsoft.XMLHTTP");
																				
		for(var i=0;i<xmlHttpVersions.length&&!xmlHttp;i++)
		{
			try
			{
				xmlHttp=new ActiveXObject(xmlHttpVersions[i]);
			}
			catch(e){}
		}
	}
	return xmlHttp;
}

function process(pageName)
{
	if(xmlHttp)
	{
		try
		{
			xmlHttp.open("GET","/index/index?pageName="+pageName,true);
			xmlHttp.onreadystatechange=doRequest;
			xmlHttp.send(null);
		}
		catch(e)
		{
			alert("Can't connect to server:\n"+e.toString());
		}
	}
}
//处理HTTP响应的函数
function doRequest()
{
	
	if(xmlHttp.readyState==4)
	{
		if(xmlHttp.status==200)
		{
			try
			{
				getResponseXml();
			}
			catch(e)
			{
				alert("Error reading the response:"+e.toString());
			}
		}
	}
}

var subvalue = "";
function oncheck(val)
{
	subvalue += val.value+",";
	alert(subvalue);
}
//解析XML文档,并用DOM模型动态创建表格的函数
function getResponseXml()
{
		var response=xmlHttp.responseXML;
		delRow();
		var tbo=document.getElementById("tbo");
		var root=response.documentElement;
		for(var i=0;i<root.childNodes.length;i++)
		{
			if(root.childNodes[i].nodeType==1)
			{
				tbo.insertRow(i);
			}
			for(var j=0;j<root.childNodes[i].childNodes.length;j++)
			{
				tbo.rows[i].insertCell(j);
				if(root.childNodes[i].childNodes[j].nodeName == "ex_id")
				{
					var e = document.createElement("input");
					e.type="checkbox";
					e.name = "ck[]";
					e.setAttribute("onclick","oncheck(this)");
					e.value = root.childNodes[i].childNodes[j].firstChild.nodeValue;	
					tbo.rows[i].cells[j].appendChild(e);
				}
				
				tbo.rows[i].cells[j].
				appendChild(document.createTextNode(root.childNodes[i].childNodes[j].firstChild.nodeValue));
			}
		}
}
//删除XML文档中前一次分页时生成的内容
function delRow()
{
	var tbo=document.getElementById("tbo");
	for(var i=tbo.childNodes.length-1;i>=0;i--)
	{	
		tbo.deleteRow(i);
	}
}

