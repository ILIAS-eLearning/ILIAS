package chatserver;

import com.google.gson.Gson;
import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.PrintStream;
import java.net.URI;
import java.net.URLDecoder;
import java.util.HashMap;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Handler for retreivin an http query and writing the response as json string
 */
public abstract class HttpJsonHandler implements HttpHandler {

	private RemoteInstances instances;

	public HttpJsonHandler(RemoteInstances instances) {
		this.instances = instances;
	}

	/**
	 * receives an http call and forwards the call to the addressed handler
	 * 
	 * if there is no handler to handle the request, an execption response will be
	 * returned
	 * 
	 * @param he
	 * @throws IOException 
	 */
	public void handle(HttpExchange he) throws IOException {
		try {
			Map<String, Object> result;
			HttpChatCallInformation info = this.parseBackendUrl(instances, he.getRequestURI());
			try {
				result = this.handleRequest(he, info);
			} catch (Exception e) {
				result = this.getExceptionResult(e);
			}

			Gson json = new Gson();
			String resultString = json.toJson(result);

			if (info.getParams().containsKey("callback")) {
				resultString = info.getParams().get("callback") + "(" + resultString + ")";
			}
			he.getResponseHeaders().add("connection", "close");
			he.getResponseHeaders().add("content-type", "text/javascript");
			he.sendResponseHeaders(200, resultString.getBytes().length);
			try {
				he.getResponseBody().write(resultString.getBytes());
			} catch (IOException e) {
				/*
				 * ignore unexpected close of client connection
				 */
			}
		} catch (IOException e) {
			//e.printStackTrace();
			String resultString = "uncaught exception of type " + e.getClass().getName();
			he.getResponseHeaders().add("connection", "close");
			he.sendResponseHeaders(500, resultString.getBytes().length);
			he.getResponseBody().write(resultString.getBytes());
		}
		he.getResponseBody().close();
	}

	/**
	 * Utility method for converting a request query (e.g. a=1&b=2) to a map like
	 * the $_REQUEST array in PHP
	 * 
	 * @param query
	 * @return 
	 */
	private Parameters queryToMap(String query) {
		if (query == null || query.length() == 0) {
			return new Parameters();
		}
		Pattern p = Pattern.compile("[^&=]+=[^&]*");
		Matcher m = p.matcher(query);

		Parameters result = new Parameters();

		try {
			while (m.find()) {
				String part = query.substring(m.start(), m.end());
				String[] parts = part.split("=", 2);
				if (parts.length > 1) {
					result.put(URLDecoder.decode(parts[0], "utf-8"), URLDecoder.decode(parts[1], "utf-8"));
				} else {
					result.put(URLDecoder.decode(parts[0], "utf-8"), "");
				}

			}
		} catch (Exception e) {
			e.printStackTrace();
		}
		return result;
	}

	/**
	 * parses an URI returns the information needed to process the call
	 * 
	 * an URI must follow the scheme:
	 * 
	 * {address}[:port]/{frontend or backend}/{action}/{hash}/{scope_id}
	 * 
	 * frontend or backend is ignored at this point
	 * 
	 * @todo bad name... should be something like "getCallInformation"
	 * 
	 * @param instances
	 * @param uri
	 * @return 
	 */
	private HttpChatCallInformation parseBackendUrl(RemoteInstances instances, URI uri) {

		String url = uri.getPath();
		String query = uri.getRawQuery();

		// get GET-parameters
		Parameters params = queryToMap(query);

		String[] parts = url.split("/", 5);
		String hash = parts[3];

		// check for executing task on a valid instance
		if (instances.getRemoteInstanceByHash(hash) != null) {
			RemoteInstance instance = instances.getRemoteInstanceByHash(hash);

			ChatScopeList scopeList = instance.getScopeList();
			ChatScope scope;
			// use an existing scope or create a new on demand
			if (scopeList.getScopeById(Integer.parseInt(parts[4])) != null) {
				scope = scopeList.getScopeById(Integer.parseInt(parts[4]));
			} else {
				scope = new ChatScope();
				scope.setId(Integer.parseInt(parts[4]));
				scopeList.add(scope);
			}

			return new HttpChatCallInformation(instance, scope, parts[2], params);

		}

		throw new InvalidCallException();
	}

	/**
	 * Transforms an exception to an json error information object
	 * 
	 * @param e
	 * @return 
	 */
	public Map<String, Object> getExceptionResult(Exception e) {
		Map<String, Object> result = new HashMap<String, Object>();

		result.put("error", true);
		result.put("error-type", e.getClass().getName());

		ByteArrayOutputStream baos = new ByteArrayOutputStream();
		e.printStackTrace(new PrintStream(baos));

		result.put("error-trace", new String(baos.toByteArray()));

		return result;
	}

	abstract public Map<String, Object> handleRequest(HttpExchange he, HttpChatCallInformation info) throws Exception;
}
