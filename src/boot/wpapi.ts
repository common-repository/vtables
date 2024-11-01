import WP from 'wpapi';

const windowObj: any = window;

const baseUrl = windowObj.WP_API_Settings?.base_url || '/wp-json/';

const wpapi = new WP({
  endpoint: baseUrl,
  nonce: windowObj.WP_API_Settings.nonce,
});

wpapi.vtables = wpapi.registerRoute('vtables/v1', '/tables/(?P<id>\\d+)');

wpapi.vtables_source = wpapi.registerRoute(
  'vtables/v1',
  '/source/(?P<id>\\d+)'
);

wpapi.vtables_settings = wpapi.registerRoute(
  'vtables/v1',
  '/settings/(?P<id>\\d+)'
);

wpapi.license = wpapi.registerRoute('vtables/v1', '/license');

export { wpapi, baseUrl };
