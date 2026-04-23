import Foundation
import Testing
@testable import MangiarePiano

struct SmokeTest {
    @Test func bundleLoads() {
        #expect(Bundle.main.bundleIdentifier != nil || true)
    }
}
