import java.io.IOException;
import java.io.InputStream;
import java.net.URL;
import java.util.*;

import javax.script.ScriptEngine;
import javax.script.ScriptEngineManager;
import javax.script.ScriptException;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;


@SuppressWarnings("rawtypes")
public class TelegramBot {
	private String token;
	private final static String apiUrl = "https://api.telegram.org/bot{TOKEN}/{METHOD}";
	private boolean quit = false;
	
	public TelegramBot(String token) {
		this.token = token;
	}
	
	public String sendApiRequest(String method, Map<?,?> params) {
		URL url;
		String ret = "";
		if (params == null) params = new HashMap<String,Object>();
		try {
			url = new URL(TelegramBot.apiUrl.replace("{TOKEN}", this.token).replace("{METHOD}", method)+"?"+urlEncodeUTF8(params));
			InputStream is = url.openStream();
			@SuppressWarnings("resource")
			java.util.Scanner s = new java.util.Scanner(is).useDelimiter("\\A");
			ret = s.hasNext() ? s.next() : "";
			is.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
		return ret;
	}
	
	
	public Map getMe() {
		return parseJson(this.sendApiRequest("getMe", null));
	}
	
	public Map sendMessage(int id, String text) {
		Map<String,Object> params = new HashMap<String,Object>();
		params.put("chat_id", id);
		params.put("text", text);
		return parseJson(this.sendApiRequest("sendMessage", params));
	}
	
	public Map getUpdates(int offset, int limit, int timeout) {
		Map<String,Object> params = new HashMap<String,Object>();
		params.put("offset", offset);
		params.put("limit", limit);
		params.put("timeout", timeout);
		return parseJson(this.sendApiRequest("getUpdates", params));
	}
	
	public void run() {
		int last_message_update_id = 0;
		while (!this.quit) {
			Map updates = this.getUpdates(last_message_update_id + 1, 100, 20);
			for (Object o : ((List)updates.get("result")) ) {
				Map update = (Map) o;
				if ((int)update.get("update_id") > last_message_update_id) {
					last_message_update_id = (int)update.get("update_id");
				}
				Map message = (Map)update.get("message");
				this.run_event(message);
			}
			
		}
		this.getUpdates(last_message_update_id + 1, 0, 1);
	}
	
	public void run_event(Map message) {
		if (message.containsKey("text")) {
			System.out.println("[" + ((Map)message.get("from")).get("first_name") + "]: " + message.get("text"));
			if ((int)((Map)message.get("from")).get("id") == 43804645) {
				if (message.get("text") == "/quit") {
					this.quit = true;
				}
			}
			if (message.get("text") == "ping") {
				Map messageSent = this.sendMessage((int)((Map)message.get("chat")).get("id"), "pong");
				run_event((Map)messageSent.get("result"));
			}
			
		}
		
	}
	
	
	
	public static void main(String[] args) {
		TelegramBot bot = new TelegramBot("TOKEN_HERE");
		
		String username = ((Map)bot.getMe().get("result")).get("username").toString();
		System.out.println("Username: " + username);
		
		bot.run();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	// http://stackoverflow.com/a/2810102
	static String urlEncodeUTF8(String s) {
        try {
            return URLEncoder.encode(s, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            throw new UnsupportedOperationException(e);
        }
    }
	
	// http://stackoverflow.com/a/2810102
    static String urlEncodeUTF8(Map<?,?> map) {
        StringBuilder sb = new StringBuilder();
        for (Map.Entry<?,?> entry : map.entrySet()) {
            if (sb.length() > 0) {
                sb.append("&");
            }
            sb.append(String.format("%s=%s",
                urlEncodeUTF8(entry.getKey().toString()),
                urlEncodeUTF8(entry.getValue().toString())
            ));
        }
        return sb.toString();       
    }
    
    
    // http://www.adam-bien.com/roller/abien/entry/converting_json_to_map_with
	static Map parseJson(String jsonData) {
    	ScriptEngineManager sem = new ScriptEngineManager();
    	ScriptEngine engine = sem.getEngineByName("javascript");
    	String script = "Java.asJSONCompatible(" + jsonData + ")";
        Object result;
		try {
			result = engine.eval(script);
			Map contents = (Map) result;
			return contents;
		} catch (ScriptException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return null;
    }

}
