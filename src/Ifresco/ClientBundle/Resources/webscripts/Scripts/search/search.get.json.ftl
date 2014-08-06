<#import "item.lib.ftl" as itemLib />
<#macro dateFormat date>${date?string("dd MMM yyyy HH:mm:ss 'GMT'Z '('zzz')'")}</#macro>
<#escape x as jsonUtils.encodeJSONString(x)>
{
	"totalCount": ${data.totalCount},
    "items":
    [
        <#list data.items as item>
		<#if item.node??>
			<#assign node = item.node>        
			{       
			   
				"name": "${item.name!''}",
				<#if item.site??>
				"site":
				{
					"shortName": "${item.site.shortName}",
					"title": "${item.site.title}"
				},
				"container": "${item.container}",
				</#if>
				<#if item.path??>
				"path": "${item.path}",
				</#if>
				<#if data.columns??>        
				<#noescape>
				<#list data.columns as column> 
				"${column}": "${node.properties[column?string]!""}"<#if item_has_next>,</#if>                 
				</#list>
				</#noescape>
				</#if>  
				"node": <#noescape>${item.nodeJSON}</#noescape>,
				"onlineEditing": ${item.onlineEditing?string},
				"parent":
				  {
				  <#if item.parent??>
					 <#assign parentNode = item.parent.node>
					 "nodeRef": "${parentNode.nodeRef}",
					 "permissions":
					 {
						"userAccess":
						{
						<#list item.parent.userAccess?keys as perm>
						   <#if item.parent.userAccess[perm]?is_boolean>
						   "${perm?string}": ${item.parent.userAccess[perm]?string}<#if perm_has_next>,</#if>
						   </#if>
						</#list>
						}
					 }
				  </#if>
				  },

				<@itemLib.itemJSON item=item />
			}<#if item_has_next>,</#if>
		</#if>
        </#list>
    ]
}
</#escape>